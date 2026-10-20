<?php

namespace App\Services\Transmission;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Entity\Transmission;
use App\Doctrine\Entity\Warehouse;
use App\Exception\FtpConfigurationNotFoundException;
use App\Model\FtpConfiguration;
use App\Model\ProductReferenceInterface;
use App\Registry\FtpConfigurationRegistryInterface;
use App\Services\Channel\ChannelCode;
use App\Services\Filesystem\GoogleStorageTransmissionFilesystem;
use App\Services\Olvo\OlvoApi;
use App\Services\Sentry\ReportingInterface;
use App\Services\Transmission\Entrepot\SpidyService;
use App\Services\Transmission\Mail\MailService;
use App\Services\Transmission\Pickup\PickupService;
use App\Services\Transmission\Transporteur\EDelivService;
use App\Services\Transmission\Transporteur\VirService;
use App\ViewRepository\OrderTransmissionViewRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use phpseclib3\Crypt\RSA\PrivateKey;
use phpseclib3\Net\SFTP;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Sentry;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Provider\ZoneProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class PrepareTransmission
{
    private const SPIDY = 'Spidy';
    private const EDELIV = 'eDeliv';
    private const VIR = 'Vir';
    private const MAIL = 'Mail';
    private const PICKUP = 'pickup';
    private readonly string $folderSave;
    private string $sshFolder;
    private string $rootFolder;
    private ?string $file = null;
    private string $csvDir;
    private bool $dismantling = false;

    protected \DateTime $dateNow;

    public function __construct(
        private readonly SpidyService $spidyService,
        private readonly VirService $virService,
        private readonly EDelivService $eDelivService,
        private readonly MailService $mailService,
        private readonly GoogleStorageTransmissionFilesystem $googleStorageTransmissionFilesystem,
        private readonly OrderTransmissionViewRepositoryInterface $orderTransmissionViewRepository,
        private readonly ViewHandlerInterface $viewHandler,
        protected MailerInterface $mailer,
        private readonly ClientInterface $client,
        string $projectDir,
        private readonly string $apiKeyUrbantz,
        private readonly string $apiKeySecretUrbantz,
        private readonly string $urlApiUrbantz,
        private readonly EntityManagerInterface $em,
        private readonly ZoneProviderInterface $defaultTaxZoneProvider,
        private readonly ZoneMatcherInterface $zoneMatcher,
        private readonly PickupService $pickupService,
        private readonly OlvoApi $olvoApi,
        string $env,
        private readonly LoggerInterface $transmissionLogger,
        private readonly RequestStack $requestStack,
        private readonly ReportingInterface $reporting,
        private readonly FtpConfigurationRegistryInterface $ftpConfigurationRegistry,
    ) {
        $this->folderSave = $projectDir . '/src/Resources/private/';

        $this->sshFolder = $projectDir . '/config/ssh/';
        $this->sshFolder = 'prod' === $env ? $this->sshFolder . $env . '/' : $this->sshFolder . 'dev/';
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws GuzzleException
     * @throws TransportExceptionInterface
     */
    public function generateTransmission(Order $order): void
    {
        /** @var ShippingMethodInterface $shippingMethod */
        $shippingMethod = $order->getShipments()[0]->getMethod();
        $warehouse = null;
        try {
            /* gestion retour Q77 google doc */
            if (ChannelCode::CHANNEL_RETURN !== $order->getChannel()->getCode()) {
                $warehouse = $shippingMethod->getWarehouse();
            }

            $transmission = new Transmission();
            $transmission->setOrder($order);
            $transmission->setShippingMethod($shippingMethod);
            $transmission->setWarehouse($warehouse);

            $this->em->persist($transmission);

            $this->dateNow = new \DateTime(\date('Y-m-d H:i:s'));
            $this->file = \date('Y') . ltrim((string) $order->getNumber(), 0) . 'TEDIBER.sic';

            if (null !== $warehouse && null !== $warehouse->getFtpConfigurationName()) {
                $this->transmissionWarehouseAction($order, $warehouse, $transmission);
            }

            $this->transmissionShippingMethodAction($order, $shippingMethod, $transmission);

            $this->em->flush();
        } catch (ClientException|FtpConfigurationNotFoundException $e) {
            $content = 'Commande impactée : ' . $order->getNumber() . ' - ' . $e->getResponse()?->getBody()->getContents();
            $message = (new Email())
                ->subject('ERREUR génération transmission')
                ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                ->to('dev@tediber.com')
                ->text($content);

            $this->mailer->send($message);
        }
    }

    /**
     * @throws GuzzleException
     * @throws TransportExceptionInterface
     */
    public function transmissionWarehouseAction(Order $order, Warehouse $warehouse, Transmission $transmission): void
    {
        // for spidy service
        if (!empty($value = $this->encodeService($order, $this->spidyService))) {
            try {
                $this->writeData($value, $this->file, $this->spidyService);
                $this->renameFile($warehouse->getName(), $order->getNumber());

                $this->sendToFtp(
                    $this->ftpConfigurationRegistry->getFtpConfigurationByName($warehouse->getFtpConfigurationName()),
                    $order->getNumber(),
                    $this->file,
                    $this->rootFolder
                );

                // bucket cloud send file
                $cloudDeposit = $this->setToGoogleCloud($transmission);
                if (null !== $cloudDeposit) {
                    unlink($this->rootFolder);
                    $transmission->setTransmissionState(Transmission::STATE_TRANSMITTED);
                    $transmission->setRootFolderWarehouse($cloudDeposit);
                }

                $this->em->persist($transmission);
            } catch (ClientException|FtpConfigurationNotFoundException $e) {
                $content = 'Commande impactée : ' . $order->getNumber() . ' - ' . $e->getResponse()->getBody()->getContents();
                $message = (new Email())
                    ->subject('ERREUR création fichier spidy')
                    ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                    ->to('dev@tediber.com')
                    ->text($content);

                $this->reporting->reportIssue(
                    $e,
                    Sentry\Severity::error(),
                    ['Spidy' => 'PrepareTransmission::transmissionWarehouseAction'],
                    ['payload' => $content]
                );
                $this->transmissionLogger->log(LogLevel::ERROR, 'Spidy transmission -> Send EDI', [
                    'route' => 'PrepareTransmission::transmissionWarehouseAction',
                    'payload' => $content,
                ]);

                $this->mailer->send($message);
            }
        }
    }

    /**
     * @throws GuzzleException
     * @throws FtpConfigurationNotFoundException
     */
    public function transmissionShippingMethodAction(Order $order, ShippingMethod $shippingMethod, Transmission $transmission): void
    {
        $cloudDeposit = null;
        if (str_contains((string) $shippingMethod->getCode(), ShippingMethodInterface::METHOD_EDELIV)) {
            if (!empty($json = $this->jmsDataSerializer($order, $this->eDelivService))) {
                $this->renameFile(self::EDELIV, $order->getNumber());
                $this->writeData($json, $this->file, $this->eDelivService);
                $cloudDeposit = $this->setToGoogleCloud($transmission);
                $this->sendUrbantzFile($json, $order->getNumber());
            }
        }
        if (ShippingMethodInterface::METHOD_OLVO === $shippingMethod->getCode()) {
            $this->olvoApi->sendOrder($order);
        }
        if (str_contains((string) $shippingMethod->getCode(), ShippingMethodInterface::METHOD_VIR)) {
            $csv = $this->encodeService($order, $this->virService);
            array_walk_recursive(
                $csv,
                static function (&$csv): void {
                    $csv = mb_convert_encoding((string) $csv, 'Windows-1252', 'UTF-8');
                }
            );
            $this->renameFile(self::VIR, $order->getNumber());
            $this->writeData($csv, $this->file, $this->virService);
            $cloudDeposit = $this->setToGoogleCloud($transmission);
            if (null !== $shippingMethod->getFtpConfigurationName() && str_contains((string) $order->getNumber(), 'R')) {
                $ftpConfiguration = $this->ftpConfigurationRegistry->getFtpConfigurationByName($shippingMethod->getFtpConfigurationName());

                $this->sendToFtp($ftpConfiguration, $order->getNumber(), $this->file, $this->rootFolder);
            }
        }
        if (null !== $cloudDeposit) {
            unlink($this->rootFolder);
            $transmission->setRootFolderShippingMethod($cloudDeposit);
            $this->em->persist($transmission);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function encodeService(Order $order, AbstractTransmissionService $service): ?array
    {
        $decodeOrderSerialization = json_decode($this->jmsDataSerializer($order, $service), true);
        $normalizeArray = [];
        $i = 1;
        $itemCount = !empty($decodeOrderSerialization['items']) ? sizeof($decodeOrderSerialization['items']) : null;

        switch ($service) {
            case $this->pickupService:
                $parent = $this->em->getRepository(Order::class)->findOneByNumber($order->getParentNumber());
                $paymentMethods = '';

                /** @var PaymentInterface $payment */
                foreach ($parent->getPayments() as $payment) {
                    $paymentMethods .= $payment->getMethod()->getName() . ' ';
                }

                $decodeOrderSerialization['paymentMethod'] = $paymentMethods;
                $normalizeArray = $service->normalizeArray($decodeOrderSerialization, $i);
                break;
            case $this->spidyService:
                $shippingMethod = $decodeOrderSerialization['shipments'][0]['method'];
                $codeExp['Code expediteur EDI'] = 'UPS' === $shippingMethod['Pre tournée / TRANSPORTEUR'] ? 'TED' : 'SQ7';
                $decodeOrderSerialization[] = $codeExp;
                foreach ($decodeOrderSerialization['items'] as $item) {
                    $eachLineCsv = $service->normalizeArray($decodeOrderSerialization, $i);
                    unset($decodeOrderSerialization['items'][$itemCount - $i]);
                    ++$i;
                    if (ProductReferenceInterface::REFERENCE_BUILD !== $eachLineCsv['productCode']
                        && !in_array(strtolower((string) $eachLineCsv['productCode']), array_map('strtolower', ProductReferenceInterface::REPOSSESS_REFS))
                        && ProductReferenceInterface::REFERENCE_SHIPPING_PRODUCT !== $eachLineCsv['productCode']
                    ) {
                        unset($eachLineCsv['productCode']);
                        if (str_contains((string) $eachLineCsv['Pre tournée / TRANSPORTEUR'], ShippingMethodInterface::METHOD_EDELIV)) {
                            $eachLineCsv['Pre tournée / TRANSPORTEUR'] = 'EDELIV';
                        } elseif (str_contains((string) $eachLineCsv['Pre tournée / TRANSPORTEUR'], ShippingMethodInterface::METHOD_DHL)) {
                            $eachLineCsv['Pre tournée / TRANSPORTEUR'] = 'DMSU';

                            if ('CH' === $order->getShippingAddress()?->getCountryCode()) {
                                $eachLineCsv['Contre remboursement'] = 3;
                                $eachLineCsv['Montant contre remboursement'] = 0;
                                $eachLineCsv['Pre tournée / TRANSPORTEUR'] = 'DMSF';
                                $eachLineCsv['Catégorie envoi CN23'] = 'O';
                            }
                        } elseif (str_contains((string) $eachLineCsv['Pre tournée / TRANSPORTEUR'], ShippingMethodInterface::METHOD_VIR)) {
                            $eachLineCsv['Pre tournée / TRANSPORTEUR'] = 'VIR';
                        } elseif (str_contains((string) $eachLineCsv['Pre tournée / TRANSPORTEUR'], ShippingMethodInterface::METHOD_FRANCE_EXPRESS)) {
                            $eachLineCsv['Pre tournée / TRANSPORTEUR'] = 'GEXP';
                        }
                        $normalizeArray[] = $eachLineCsv;
                    }
                }
                break;
            case $this->virService:
                foreach ($decodeOrderSerialization['items'] as $item) {
                    $eachLine = $service->normalizeArray($decodeOrderSerialization, $i);

                    $header = $eachLine['header'];
                    $orderLine = $eachLine['orderLine'];
                    $itemLine = $eachLine['itemLine'];

                    if ($i <= 1) {
                        foreach ($header as $head) {
                            $normalizeArray[] = $head;
                        }
                        $normalizeArray[] = $orderLine;
                    }
                    if (ProductReferenceInterface::REFERENCE_BUILD === $itemLine['productCode']
                        && !in_array($itemLine['name'], ProductReferenceInterface::INST_REFS)
                    ) {
                        $normalizeArray[] = $itemLine;
                    } elseif (in_array($itemLine['name'], ProductReferenceInterface::INST_REFS)) {
                        $normalizeArray[] = $itemLine;
                    } elseif (ProductReferenceInterface::REFERENCE_RETURN_BUILD === $itemLine['name']) {
                        $this->dismantling = true;
                    } else {
                        $normalizeArray[] = $itemLine;
                    }

                    unset($decodeOrderSerialization['items'][$itemCount - $i]);
                    ++$i;
                }

                /** @var Shipment $firstShipment */
                $firstShipment = $order->getShipments()->first();

                /** @var ShippingMethod $shippingeMethod */
                $shippingeMethod = $firstShipment->getMethod();
                $shipingMethodCategoryCode = $shippingeMethod->getShippingMethodCategory()?->getCode() ?? '';

                foreach ($normalizeArray as &$array) {
                    if (isset($array['Prestation'])) {
                        if (str_contains((string) $shipingMethodCategoryCode, ShippingMethodInterface::CATEGORY_EXPRESS_ENCOMBRANT)
                            || str_contains((string) $shipingMethodCategoryCode, ShippingMethodInterface::CATEGORY_EXPRESS_PETIT_COLIS)) {
                            $array['Prestation'] = 'LC';
                        } elseif (str_contains((string) $shipingMethodCategoryCode, ShippingMethodInterface::CATEGORY_CONFORT)) {
                            $array['Prestation'] = 'LS';
                        } elseif (str_contains((string) $shipingMethodCategoryCode, ShippingMethodInterface::CATEGORY_PREMIUM)) {
                            $array['Prestation'] = 'LM';
                        } elseif (str_contains((string) $shipingMethodCategoryCode, ShippingMethodInterface::CATEGORY_GRAND_OURS)) {
                            $array['Prestation'] = 'LI';
                        } elseif ('return' === $order->getChannel()->getCode()) {
                            $array['Prestation'] = true === $this->dismantling ? 'RM' : 'RS';
                        }
                    }
                }
                break;
        }

        return $normalizeArray;
    }

    public function writeData($datas, $file, $service): void
    {
        switch ($service) {
            case $this->spidyService:
                if ($this->getRootFolder($this->file, self::SPIDY)) {
                    $this->putHeader($datas);
                    $this->writeInCsv($datas);
                }
                break;
            case $this->eDelivService:
                if ($this->getRootFolder($this->file, self::EDELIV)) {
                    $this->writeFile($datas);
                }
                break;
            case $this->virService:
                if ($this->getRootFolder($this->file, self::VIR)) {
                    $this->writeInCsv($datas, true);
                }
                unset($datas['header']);
                break;
        }
    }

    /**
     * Write header CSV.
     */
    public function putHeader($csv): void
    {
        // first header creation
        $csv_file = fopen($this->rootFolder, 'w+');
        fputcsv($csv_file, array_keys($csv[0]), ';');
        fclose($csv_file);
    }

    public function jmsDataSerializer(Order $order, AbstractTransmissionService $service): string
    {
        $context = new Context();
        switch ($service) {
            case $this->spidyService:
                $context->setGroups([strtolower(self::SPIDY)]);
                break;
            case $this->virService:
                $context->setGroups([strtolower(self::VIR)]);
                break;
            case $this->eDelivService:
                $context->setGroups([self::EDELIV]);
                break;
            case $this->mailService:
                $context->setGroups([self::MAIL]);
                break;
            case $this->pickupService:
                $context->setGroups([self::PICKUP]);
                break;
        }
        try {
            $view = View::create($this->orderTransmissionViewRepository->getOneById($order->getId()), Response::HTTP_OK);
            $view->setContext($context);
            $view->setFormat('json');

            $request = $this->requestStack->getCurrentRequest();
            if (null === $request) {
                $request = new Request();
            }

            return $this->viewHandler->handle($view, $request)->getContent();
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function writeInCsv($csv, bool $removeQuotes = false): void
    {
        $csv_file = fopen($this->rootFolder, 'a');
        // fprintf($csv_file, chr(0xEF).chr(0xBB).chr(0xBF));
        foreach ($csv as $line) {
            $removeQuotes ? fwrite($csv_file, implode(';', $line) . "\r\n") : fputcsv($csv_file, $line, ';');
        }
        fclose($csv_file);
    }

    public function writeFile($data): void
    {
        $file = fopen($this->rootFolder, 'a');
        fwrite($file, (string) $data);
        fclose($file);
    }

    public function getRootFolder($file, $subfolder, $subsubfolder = null): string
    {
        $dossier = $this->dateNow->format('Y') . '/' . $this->dateNow->format('F');
        $this->csvDir = "CSV/Livraisons/{$subfolder}/{$dossier}/{$subsubfolder}";
        $this->rootFolder = $this->folderSave . $this->csvDir . $file;
        $this->makedirs($this->folderSave . $this->csvDir);

        return $this->rootFolder;
    }

    public function makedirs($dirpath, $mode = 0777): bool
    {
        return is_dir($dirpath) || mkdir($dirpath, $mode, true);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function setToGoogleCloud(Transmission $transmission): ?string
    {
        $fileName = str_replace('../../', ' ', $this->csvDir . $this->file);
        $uploaded = $this->googleStorageTransmissionFilesystem->upload($this->rootFolder, $fileName);

        if (!$uploaded) {
            $message = (new Email())
                ->subject(sprintf('ERREUR envoie vers Google Cloud%s', $fileName))
                ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                ->to('dev@tediber.com')
                ->text(
                    sprintf('Problème de dépôt de fichier sur storage Cloud %s', $this->file)
                );

            $this->mailer->send($message);

            $transmission->setTransmissionState(Transmission::STATE_FAILED);

            $this->transmissionLogger->log(LogLevel::ERROR, 'EDI transmission -> Send to Google', [
                'route' => 'PrepareTransmission::setToGoogleCloud',
                'payload' => $fileName,
            ]);

            return null;
        }

        return $fileName;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendUrbantzFile(string $order, string $number): void
    {
        try {
            $this->client->post($this->urlApiUrbantz, [
                'auth' => [
                    $this->apiKeyUrbantz,
                    $this->apiKeySecretUrbantz,
                ],
                'json' => [
                    json_decode($order, true),
                ],
            ]);
        } catch (ClientException $e) {
            $content = 'Commande impactée : ' . $number . ' - ' . $e->getResponse()?->getBody()->getContents();
            $message = (new Email())
                ->subject('ERREUR transmission Api Urbantz')
                ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                ->to('dev@tediber.com')
                ->text($content);

            $this->mailer->send($message);
        } catch (\Exception $exception) {
            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                ['Edeliv' => 'transmission problem'],
                ['Error message' => $exception->getMessage()]
            );
        }
    }

    public function renameFile($type, string $orderNumber): void
    {
        switch ($type) {
            case self::SPIDY:
                $this->file = 'SHIPUTF_' . \date('ymd') . ltrim($orderNumber, 0) . 'ted.csv';

                break;
            case self::EDELIV:
                $this->file = 'Urbantz' . ltrim($orderNumber, 0) . '.json';

                break;
            case self::VIR:
                $dateVir = new \DateTime();
                $datevirString = $dateVir->format('ymd') . ltrim($orderNumber, 0);
                $this->file = 'VIR041TEDI' . $datevirString . '.csv';
                break;
        }
    }

    public function sendToFtp(FtpConfiguration $ftpConfiguration, $number, $remoteFile, $rootFolder, bool $secure = true)
    {
        $message = false;
        $response = true;

        if (true === $secure) {
            $sftp = new SFTP($ftpConfiguration->host, $ftpConfiguration->port ?? 22);

            if ($ftpConfiguration->hasPrivateKey) {
                $sshKeyPath = $this->sshFolder . strtolower($ftpConfiguration->ftpConfigurationName->value);
                $password = PrivateKey::loadPrivateKey(file_get_contents($sshKeyPath));
            } else {
                $password = $ftpConfiguration->password;
            }

            try {
                $login = $sftp->login($ftpConfiguration->user, $password);
            } catch (\Exception) {
                $login = false;
            }

            if (!$login
                || !$sftp->chdir($ftpConfiguration->directory)
                || !$sftp->put($remoteFile, $rootFolder, SFTP::SOURCE_LOCAL_FILE)) {
                $message = (new Email())
                    ->subject(sprintf('ERREUR transmission %s %s', $ftpConfiguration->ftpConfigurationName->value, $number))
                    ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                    ->to('dev@tediber.com')
                    ->text(
                        'Commande non transmise , pas de connexion base de donnée.'
                    );

                $this->reporting->reportIssue(
                    'SFTP Transmission Error',
                    Sentry\Severity::error(),
                    ['transmission_ftp' => $number],
                    ['ftp_configuration' => [
                            'host' => $ftpConfiguration->host,
                            'port' => $ftpConfiguration->port,
                            'username' => $ftpConfiguration->user,
                            'password' => $ftpConfiguration->password,
                            'directory' => $ftpConfiguration->directory,
                        ],
                    ]
                );
            }
        } else {
            $connect = ftp_connect($ftpConfiguration->host);
            $login = ftp_login($connect, $ftpConfiguration->user, $ftpConfiguration->password);
            ftp_pasv($connect, true);
            if (!$connect && !$login) {
                $message = (new Email())
                    ->subject('ERREUR transmission ' . $ftpConfiguration->ftpConfigurationName->value . ' ' . $number)
                    ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                    ->to('dev@tediber.com')
                    ->text(
                        'Commande non transmise , pas de connexion au serveur.'
                    );

                $this->reporting->reportIssue(
                    'FTP Transmission Error',
                    Sentry\Severity::error(),
                    ['transmission_ftp' => $number],
                    ['ftp_configuration' => [
                            'host' => $ftpConfiguration->host,
                            'port' => $ftpConfiguration->port,
                            'username' => $ftpConfiguration->user,
                            'password' => $ftpConfiguration->password,
                            'directory' => $ftpConfiguration->directory,
                        ],
                    ]
                );
            } else {
                ftp_chdir($connect, $ftpConfiguration->directory);
                $response = ftp_put($connect, $remoteFile, $rootFolder, FTP_ASCII);
            }
        }

        if (false !== $message) {
            $this->reporting->reportIssue(
                'Problem with file transmission ftp',
                Sentry\Severity::error(),
                ['transmission_ftp' => $number],
                ['payload' => $this->file]
            );

            $this->mailer->send($message);
        } else {
            return $response;
        }
    }

    public function getSpecificZone(Order $order): ?ZoneInterface
    {
        $shippingAddress = $order->getShippingAddress();
        $zone = null;

        if (null !== $shippingAddress) {
            $zone = $this->zoneMatcher->match($shippingAddress, Scope::SHIPPING);
        }

        return $zone ?: $this->defaultTaxZoneProvider->getZone($order);
    }
}
