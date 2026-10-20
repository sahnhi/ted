<?php

namespace App\Services\Cron\Transmission;

use App\Doctrine\Entity\OrderItemLogistic;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Entity\Transmission;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Enum\FtpConfigurationName;
use App\Exception\FtpConfigurationNotFoundException;
use App\Exception\Order\UnresolvableOrderNumberException;
use App\Model\FtpConfiguration;
use App\Model\ProductReferenceInterface;
use App\Registry\FtpConfigurationRegistryInterface;
use App\Resolver\Order\OrderNumberResolverInterface;
use App\Services\Admin\Export\ExportCsv;
use App\Services\Filesystem\GoogleStorageEdiFilesystem;
use App\Services\OrderItem\OrderItemService;
use App\Services\Sentry\Reporting;
use App\Services\Transmission\PrepareTransmission;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use phpseclib3\Net\SFTP;
use Sentry;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderInterface as OrderInterfaceAlias;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address as MailerAddress;
use Symfony\Component\Mime\Email;

class PackingProcessor implements PackingProcessorInterface
{
    private string $number;

    private array $ediErrors = [];

    private FtpConfiguration $ftpConfiguration;

    private SFTP $sftp;

    private readonly string $inventoryCsvRemoteName;

    private readonly string $privateFolder;

    public function __construct(
        private readonly FtpConfigurationRegistryInterface $ftpConfigurationRegistry,
        private readonly PrepareTransmission $prepareTransmission,
        private readonly ExportCsv $exportCsv,
        private readonly OrderRepository $orderRepository,
        private readonly MailerInterface $mailer,
        private readonly RepositoryInterface $transmissionRepository,
        private readonly GoogleStorageEdiFilesystem $googleStorageFilesystem,
        private readonly string $packingFolder,
        private readonly EntityManagerInterface $entityManager,
        private readonly Reporting $reporting,
        private readonly OrderNumberResolverInterface $orderNumberResolver,
        string $projectDir,
        private readonly OrderItemService $orderItemService,
    ) {
        $this->privateFolder = $projectDir . '/src/Resources/private/';

        /* CRON by hour & CR_PRE not deleting in remote * */
        $this->inventoryCsvRemoteName = 'CR_PRE' . date('Ym');
    }

    /**
     * @throws FtpConfigurationNotFoundException
     */
    public function prepare(): void
    {
        $this->initSftp();

        $dirPackingPath = $this->ftpConfiguration->ftpConfigurationName->value . '/' . \date('Y/') . \date('F') . '/';
        $packingLocalPath = $this->privateFolder . self::PACKING_LOCAL_FOLDER . $dirPackingPath;

        $files = $this->sftp->nlist($this->packingFolder);

        if (false !== $files) {
            foreach ($files as $file) {
                if (in_array($file, ['..', '.'])) {
                    continue;
                }

                $localFile = $packingLocalPath . $file;

                /* check file is CR_PRE * */
                if (preg_match("#$this->inventoryCsvRemoteName(.*?)\.csv#is", (string) $file) && !str_ends_with((string) $file, '.old')) {
                    $this->prepareTransmission->makedirs($packingLocalPath);

                    $this->sftp->get($this->packingFolder . $file, $localFile);

                    if (file_exists($localFile)) {
                        $packingTodoFolder = self::PACKING_GOOGLE_FOLDER . self::GOOGLE_FOLDER_TODO;

                        $this->setToGoogleCloud($packingTodoFolder . $dirPackingPath . $file, $packingLocalPath . $file);
                        unlink($packingLocalPath . $file);

                        $this->sftp->rename($this->packingFolder . $file, $this->packingFolder . $file . '.old');
                    }
                }
            }
        }
    }

    /**
     * @throws NonUniqueResultException
     * @throws FtpConfigurationNotFoundException
     * @throws NoResultException
     */
    public function process(): void
    {
        $ftpConfiguration = $this->getFtpConfigurationForSpidy();

        $packingFolder = self::PACKING_GOOGLE_FOLDER . $ftpConfiguration->ftpConfigurationName->value;
        $packingTodoFolder = self::PACKING_GOOGLE_FOLDER . self::GOOGLE_FOLDER_TODO;
        $packingIgnoreFolder = self::PACKING_GOOGLE_FOLDER . self::GOOGLE_FOLDER_IGNORED;

        $storageFiles = $this->googleStorageFilesystem->listKeys($packingTodoFolder);

        foreach ($storageFiles as $storageFile) {
            $moved = false;
            if (preg_match("#$this->inventoryCsvRemoteName(.*?)\.CSV#is", (string) $storageFile)) {
                $packingEdi = $this->pushPacking($storageFile);

                /* save to cloud * */
                if (null !== $packingEdi && file_exists($this->privateFolder . $packingEdi) && 'skip' !== $packingEdi) {
                    if ($this->setToGoogleCloud($packingEdi, $this->privateFolder . $packingEdi)) {
                        $this->googleStorageFilesystem->moveAndReplace(
                            $storageFile,
                            $packingFolder . '/' . \date('Y/') . \date('F') . '/' . basename((string) $storageFile)
                        );

                        $moved = true;
                        /* send Vir file with packing * */
                        if ($this->sendToPacking($this->number, $this->privateFolder . $packingEdi)) {
                            unlink($this->privateFolder . $packingEdi);
                        } else {
                            $this->ediErrors[] = $packingEdi;
                        }
                    }
                }
            }

            // Ignore file
            if (!$moved && str_contains((string) $storageFile, '.CSV')) {
                $dirPackingPath = $ftpConfiguration->ftpConfigurationName->value . '/' . \date('Y/') . \date('F') . '/';
                $this->googleStorageFilesystem->moveAndReplace(
                    $storageFile,
                    $packingIgnoreFolder . $dirPackingPath . basename((string) $storageFile)
                );
            }
        }

        if (!empty($this->ediErrors)) {
            $body = "Les EDI suivants n'ont pas été déposés sur le serveur FTP de VIR: <br />";
            foreach ($this->ediErrors as $row) {
                $body .= $row . '<br />';
            }

            $this->sendErrorMail('ERREUR dépôt EDI VIR', $body);
        }
    }

    /**
     * Ftp details recuperation & connexion.
     *
     * @throws FtpConfigurationNotFoundException
     */
    private function initSftp(): void
    {
        $this->ftpConfiguration = $this->getFtpConfigurationForSpidy();
        $sftp = new SFTP($this->ftpConfiguration->host, 22);
        $sftp->login($this->ftpConfiguration->user, $this->ftpConfiguration->password);

        $this->sftp = $sftp;
    }

    /**
     * @throws FtpConfigurationNotFoundException
     */
    private function getFtpConfigurationForSpidy(): FtpConfiguration
    {
        return $this->ftpConfigurationRegistry->getFtpConfigurationByName(FtpConfigurationName::SPIDY);
    }

    private function pushPacking($packingFile): ?string
    {
        $tmpRow = [];
        $packingData = $this->googleStorageFilesystem->readTemporaryFile($packingFile);
        $packingData = $this->exportCsv->readCsv($packingData, ';');
        $packingCodes = $itemsLineArray = $codesUsed = $batchNumbers = $build = $packs = [];

        try {
            $this->number = $this->orderNumberResolver->resolve($packingData);
        } catch (UnresolvableOrderNumberException $exception) {
            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                ['Packing' => $packingFile]
            );

            return null;
        }

        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneLikeNumber($this->number);

        if (null === $order) {
            $this->reporting->reportIssue(
                sprintf('No order found for order number "%s"', $this->number),
                Sentry\Severity::warning(),
                ['Packing' => $packingFile],
                ['order' => $this->number]
            );

            return null;
        }
        /** @var Order $order */
        if (empty($order->getOrderItemLogistics())) {
            $this->orderItemService->generateItemLogistic($order);

            return 'skip';
        }
        if (OrderInterface::STATE_CANCELLED === $order->getState()) {
            return null;
        }

        /** @var Shipment $shipment */
        $shipment = $order->getShipments()->first();

        foreach ($packingData as $data) {
            /* Packing Line in CR_PRE file * */
            if (isset($data[3])) {
                if ('DCO' === $data[0]) {
                    $variantCode = trim((string) $data[7]);
                    if (!str_contains($variantCode, 'TEDMPH')) {
                        $variantCode = str_replace('TEDM', 'TED', $variantCode);
                    }
                    /* End */
                    if (isset($data[self::CR_PRE_PACKING_CODE_COLUMN])) {
                        $packingCode = trim((string) $data[self::CR_PRE_PACKING_CODE_COLUMN]);
                        if (!in_array($packingCode, $codesUsed, true)) {
                            $packingCodes[$variantCode][] = $packingCode;
                            $codesUsed[] = $packingCode;
                        } else {
                            $packingCodes[$variantCode][] = null;
                        }
                    }
                    if (isset($data[self::CR_PRE_BATCH_NUMBER_COLUMN])) {
                        $batchNumbers[$variantCode][] = $this->clean(trim((string) $data[self::CR_PRE_BATCH_NUMBER_COLUMN]));
                    }
                }
            }
        }

        if (0 === count($batchNumbers)) {
            $this->reporting->reportIssue(
                'No batch numbers to save',
                Sentry\Severity::warning(),
                ['Packing' => $packingFile],
                ['order' => $this->number]
            );
        } else {
            $packs = $this->saveBatchNumbers($order, $batchNumbers);
        }

        $builds = $this->getBuildQuantityFromOrder($packs);

        /* check if it is the right shippingMethod * */
        if (!empty($order->getShipments()[0]) && !str_contains((string) $order->getShipments()[0]->getMethod()->getCode(), self::SHIPPING_METHOD_ELIGIBLE)) {
            return null;
        }

        /** need folder by transmission save **/
        /** @var Transmission $transmission */
        $transmission = $this->transmissionRepository->findOneBy([
            'order' => $order,
        ]);

        if (null === $transmission) {
            $this->ediErrors[] = sprintf('Transmission not found for order "%s"', $order->getId());

            return null;
        }

        $shippingRootFolder = $transmission->getRootFolderShippingMethod();

        $localShipmentEdi = $this->googleStorageFilesystem->download($shippingRootFolder, $shippingRootFolder);

        $eDiData = $this->exportCsv->readCsv($this->privateFolder . $localShipmentEdi, ';');

        /** @var Address $address */
        $address = $order->getShippingAddress();

        $comment = str_replace(["\r", "\n"], ' ', (string) $address->getDetails());
        $details = str_split($comment, 70);

        /* deleting items line we'll rewrite packing  * */
        switch ($shipment->getMethod()->getCode()) {
            case str_contains((string) $shipment->getMethod()->getCode(), ShippingMethodInterface::METHOD_VIR):
                foreach ($eDiData as $key => $data) {
                    if ('E' === $data[0]) {
                        $tmpRow = $data;

                        $dateFormat = str_replace('/', '-', (string) $data[22]);
                        $dateTime = strtotime($dateFormat);
                        $date = date('Y-m-d', $dateTime);
                        $date = new \DateTime($date);
                        $tmpRow[self::VIR_CSV_COLUMN['orderDate']] = $date->format('d/m/y');
                        $tmpRow[self::VIR_CSV_COLUMN['slotDate']] = $shipment->getStartDeliverySlot()->format('d/m/y');
                        $tmpRow[self::VIR_CSV_COLUMN['slotTime']] = $shipment->getStartDeliverySlot()->format('H') . '/' . $shipment->getEndDeliverySlot()->format('H');
                        $tmpRow[self::VIR_CSV_COLUMN['comment1']] = (!empty($details[0])) ? $details[0] : '';
                        $tmpRow[self::VIR_CSV_COLUMN['comment2']] = (!empty($details[1])) ? $details[1] : '';
                        $tmpRow[self::VIR_CSV_COLUMN['difficulty']] = (!empty($details[3])) ? substr($details[3], 0, 35) : '';

                        /* Address */
                        if (!empty($order->getShippingAddress())) {
                            $shippingAddress = $order->getShippingAddress();
                            $toReplace = ["\r\n", "\n", "\r"];
                            $street = str_replace($toReplace, ' ', (string) $shippingAddress->getStreet());
                            $tmpRow[self::VIR_CSV_COLUMN['street']] = trim($this->getAddress($street));
                            $tmpRow[self::VIR_CSV_COLUMN['street2']] = trim($this->getAddress($street, false));
                            $tmpRow[self::VIR_CSV_COLUMN['fullname']] = mb_convert_encoding($shippingAddress->getFirstName() . ' ' . $shippingAddress->getLastName(), self::ENCODING_WINDOWS, self::ENCODING_UTF8);
                            $tmpRow[self::VIR_CSV_COLUMN['city']] = mb_convert_encoding((string) $shippingAddress->getCity(), self::ENCODING_WINDOWS, self::ENCODING_UTF8);
                            $tmpRow[self::VIR_CSV_COLUMN['phoneNumber']] = $shippingAddress->getPhoneNumber();
                        }
                        unset($eDiData[$key]);
                    }
                    if ('A' === $data[0]) {
                        $itemsLineArray[] = $data;
                        unset($eDiData[$key]);
                    }
                    if (empty($data[0])) {
                        unset($eDiData[$key]);
                    }
                }

                $eDiData[] = $tmpRow;
                if (!empty($packingCodes)) {
                    /** get only packing line **/
                    $packingEditLine = $this->organizeVirPackingCode($packingCodes, $itemsLineArray, $builds);
                } else {
                    $packingEditLine = $itemsLineArray;
                }

                /* add to EDI data packing line organized * */
                $eDiData[] = $packingEditLine;
                break;
        }

        $this->exportCsv->createCsv($this->privateFolder . $shippingRootFolder, $eDiData, true, false);

        return $shippingRootFolder;
    }

    private function setToGoogleCloud(string $googleRootFolder, string $localPath): bool
    {
        try {
            $this->googleStorageFilesystem->upload($localPath, $googleRootFolder);

            return true;
        } catch (\Exception $e) {
            $this->sendErrorMail(
                'ERREUR envoi Colisage vers Google Cloud ' . $googleRootFolder,
                'Problème de dépôt de fichier sur storage Cloud ' . $localPath . ' erreur de type: ' . error_log($e->getMessage())
            );

            return false;
        }
    }

    /**
     * @return array|false|string[]
     */
    private function splitFileAndFolder(string $completePath)
    {
        return preg_split('~/(?=[^/]*$)~', $completePath);
    }

    private function organizeVirPackingCode($packingCode, $itemsLineArray, $builds): array
    {
        $virPacking = [];

        foreach ($itemsLineArray as $line) {
            if (isset($line[3]) && !empty($packingCode[$line[3]])) {
                $rowQuantity = (int) $line[self::VIR_CSV_COLUMN['quantity']];
                if ($rowQuantity > 1) {
                    for ($i = 0; $i < $rowQuantity; ++$i) {
                        $line[self::VIR_CSV_COLUMN['packingCode']] = array_shift($packingCode[$line[3]]);
                        $line[self::VIR_CSV_COLUMN['quantity']] = 1;
                        $line[self::VIR_CSV_COLUMN['lineNumber']] = $line[self::VIR_CSV_COLUMN['wrongLineNumber']];
                        $line[self::VIR_CSV_COLUMN['shippedQuantity']] = $line[self::VIR_CSV_COLUMN['quantity']];
                        $line[self::VIR_CSV_COLUMN['wrongLineNumber']] = '';
                        $virPacking[] = $line;
                    }
                } else {
                    $line[self::VIR_CSV_COLUMN['packingCode']] = array_shift($packingCode[$line[3]]);
                    $line[self::VIR_CSV_COLUMN['lineNumber']] = $line[self::VIR_CSV_COLUMN['wrongLineNumber']];
                    $line[self::VIR_CSV_COLUMN['shippedQuantity']] = $line[self::VIR_CSV_COLUMN['quantity']];
                    $line[self::VIR_CSV_COLUMN['wrongLineNumber']] = '';
                    $virPacking[] = $line;
                }
            } else {
                $line[self::VIR_CSV_COLUMN['lineNumber']] = $line[self::VIR_CSV_COLUMN['wrongLineNumber']];
                $line[self::VIR_CSV_COLUMN['shippedQuantity']] = $line[self::VIR_CSV_COLUMN['quantity']];
                $line[self::VIR_CSV_COLUMN['wrongLineNumber']] = '';
                $virPacking[] = $line;
            }
        }
        foreach ($virPacking as &$row) {
            if ('' === $row[self::VIR_CSV_COLUMN['packingCode']] || 'N' === $row[self::VIR_CSV_COLUMN['stockType']]) {
                $row[self::VIR_CSV_COLUMN['packingCode']] = '';
                $row[self::VIR_CSV_COLUMN['shippedQuantity']] = '0';
                $row[self::VIR_CSV_COLUMN['stockType']] = 'N';
            } elseif (ProductReferenceInterface::REFERENCE_BUILD === $row[self::VIR_CSV_COLUMN['productCode']]) {
                $row[self::VIR_CSV_COLUMN['packingCode']] = '';
                $row[self::VIR_CSV_COLUMN['quantity']] = $builds[$row[self::VIR_CSV_COLUMN['variantCode']]]['quantity'];
                $row[self::VIR_CSV_COLUMN['stockType']] = 'N';
            }
        }

        return $virPacking;
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws FtpConfigurationNotFoundException
     */
    private function sendToPacking(string $number, string $file): bool
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneLikeNumber($number, OrderInterfaceAlias::STATE_CANCELLED);
        $shipment = $order->getShipments()->first();

        /** @var ShippingMethodInterface $method */
        $method = $shipment->getMethod();

        $ftpConfiguration = $this->ftpConfigurationRegistry->getFtpConfigurationByName($method->getFtpConfigurationName());
        $parts = $this->splitFileAndFolder($file);
        $secure = null !== $ftpConfiguration->port;

        return $this->prepareTransmission->sendToFtp($ftpConfiguration, $number, $parts[1], $file, $secure);
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendErrorMail(string $subject, string $body): void
    {
        $message = (new Email())
            ->subject($subject)
            ->from(new MailerAddress('support@tediber.com', 'TEDIBER Sylius'))
            ->to('dev@tediber.com')
            ->html($body);

        $this->mailer->send($message);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function saveBatchNumbers(Order $order, array $batchNumbers): ?array
    {
        $packs = [];
        if (!empty($batchNumbers)) {
            /** @var OrderItem $item */
            foreach ($order->getItems() as $item) {
                $packs[$item->getVariant()->getProduct()->getCode()][$item->getVariant()->getCode()]['quantity'] = $item->getQuantity();
                /** @var ProductVariantInterface $variant */
                $variant = $item->getVariant();
                foreach ($variant->getPacks() as $pack) {
                    $packs[$item->getVariant()->getProduct()->getCode()][$item->getVariant()->getCode()]['packId'][] = $pack->getId();
                }
                if (0 === count($item->getOrderItemLogistics())) {
                    $this->reporting->reportIssue(
                        'No order Items Logistic exists for order #' . $order->getNumber(),
                        Sentry\Severity::error(),
                        ['order' => $order->getNumber()],
                    );
                } else {
                    /** @var OrderItemLogistic $orderItemLogistic */
                    foreach ($item->getOrderItemLogistics() as $orderItemLogistic) {
                        if (!empty($batchNumbers[$orderItemLogistic->getProductVariantLogistic()->getCode()])) {
                            $batchNumber = array_shift($batchNumbers[$orderItemLogistic->getProductVariantLogistic()->getCode()]);
                            $orderItemLogistic->setBatchNumber($batchNumber);

                            $this->entityManager->persist($orderItemLogistic);
                        }
                    }
                }
            }
            $this->entityManager->flush();

            if (0 !== count($batchNumbers)) {
                $this->reporting->reportIssue(
                    'Some batch numbers were not set',
                    Sentry\Severity::warning(),
                    ['Packing' => $order->getNumber()],
                    [
                        'order' => $order->getNumber(),
                        'remainingBatchNumbers' => $batchNumbers,
                    ]
                );
            }
        }

        return $packs;
    }

    /**
     * Format address length for EDI.
     */
    private function getAddress(string $street, bool $first = true): string
    {
        $posReturnLine = strpos($street, "\n");

        if (false !== $posReturnLine && $posReturnLine < self::LIMIT_ADDRESS1) {
            if ($first) {
                return mb_substr($street, 0, $posReturnLine, 'UTF-8');
            } else {
                return mb_substr($street, $posReturnLine, self::LIMIT_ADDRESS2 - (self::LIMIT_ADDRESS1 - $posReturnLine), 'UTF-8');
            }
        } else {
            if ($first) {
                return mb_substr($street, 0, self::LIMIT_ADDRESS1, 'UTF-8');
            } else {
                return mb_substr($street, self::LIMIT_ADDRESS1, self::LIMIT_ADDRESS2, 'UTF-8');
            }
        }
    }

    private function getBuildQuantityFromOrder($packs)
    {
        if (!empty($packs['build'])) {
            $builds = $packs['build'];
            unset($packs['build']);
            foreach ($builds as &$build) {
                $build['quantity'] = 0;
                foreach ($packs as $pack) {
                    foreach ($pack as $row) {
                        if ((!empty($row['packId']) && !empty($build['packId'])) && !empty(array_intersect($row['packId'], $build['packId']))) {
                            $build['quantity'] += $row['quantity'];
                        }
                    }
                }
            }

            return $builds;
        } else {
            return false;
        }
    }

    private function clean(string $string): string
    {
        $string = str_replace(' ', '-', $string);

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }
}
