<?php

namespace App\Services\Cron\Transmission;

use App\Context\CliBasedChannelContext;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Enum\FtpConfigurationName;
use App\Exception\FtpConfigurationNotFoundException;
use App\Model\FtpConfiguration;
use App\Registry\FtpConfigurationRegistryInterface;
use App\Services\Admin\Export\ExportCsv;
use App\Services\Drip\Batch\DripBatchFulfilled;
use App\Services\Filesystem\GoogleStorageEdiFilesystem;
use App\Services\Transmission\PrepareTransmission;
use App\Services\Transmission\Tracking\AssignTrackingUrl;
use Doctrine\Persistence\ObjectManager;
use phpseclib3\Net\SFTP;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class TrackingProcessor implements TrackingProcessorInterface
{
    private readonly string $privateFolder;

    private readonly string $inventoryCsvRemoteName;

    public function __construct(
        private readonly FtpConfigurationRegistryInterface $ftpConfigurationRegistry,
        private readonly PrepareTransmission $prepareTransmission,
        private readonly ExportCsv $exportCsv,
        private readonly OrderRepository $orderRepository,
        private readonly ObjectManager $orderManager,
        private readonly MailerInterface $mailer,
        private readonly DripBatchFulfilled $dripBatchFulfilled,
        private readonly AssignTrackingUrl $assignTrackingUrl,
        private readonly GoogleStorageEdiFilesystem $googleStorageFilesystem,
        string $projectDir,
        private readonly CliBasedChannelContext $cliBasedChannelContext,
    ) {
        $this->privateFolder = $projectDir . '/src/Resources/private/';
        /* CRON by hour & CR_TOU not deleting in remote * */
        $this->inventoryCsvRemoteName = 'CR_TOU' . date('Ymd');
    }

    /**
     * @throws FtpConfigurationNotFoundException
     */
    public function prepare(): void
    {
        $ftpConfiguration = $this->getFtpConfigurationForSpidy();

        if (null !== $ftpConfiguration->trackingPath) {
            $sftp = new SFTP($ftpConfiguration->host, 22);
            $sftp->login($ftpConfiguration->user, $ftpConfiguration->password);
            $files = $sftp->nlist($ftpConfiguration->trackingPath);

            $ftpName = $ftpConfiguration->ftpConfigurationName->value;
            $monthFolderPath = $this->getMonthFolderPath();
            $trackingLocalPath = sprintf('%s%s%s%s%s', $this->privateFolder, self::TRACKING_LOCAL_FOLDER, $ftpName, \DIRECTORY_SEPARATOR, $monthFolderPath);

            /* check if not empty directory */
            if (false !== $files) {
                foreach ($files as $file) {
                    if (in_array($file, ['..', '.'])) {
                        continue;
                    }

                    $extension_available_search = implode('|', self::EXTENSION_AVAILABLE);

                    /* check if extension is like  .CSV(tracking Spidy) and .TXT(Tracking vir) */
                    if (preg_match('/^.*\.(' . $extension_available_search . ')$/i', (string) $file)) {
                        $this->prepareTransmission->makedirs($trackingLocalPath);

                        if ($sftp->get($ftpConfiguration->trackingPath . $file, $trackingLocalPath . '/' . $file)) {
                            $sftp->rename($ftpConfiguration->trackingPath . $file, $ftpConfiguration->trackingPath . $file . '.old');
                        }

                        if (file_exists($trackingLocalPath . '/' . $file)) {
                            $trackingTodoFolder = self::TRACKING_GOOGLE_FOLDER . self::GOOGLE_FOLDER_TODO . $ftpName . \DIRECTORY_SEPARATOR . $monthFolderPath;

                            $this->setToGoogleCloud($trackingLocalPath . '/' . $file, $trackingTodoFolder . $file);
                            unlink($trackingLocalPath . '/' . $file);
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws FtpConfigurationNotFoundException
     */
    public function process(): void
    {
        $ftpConfiguration = $this->getFtpConfigurationForSpidy();

        $trackingFolder = self::TRACKING_GOOGLE_FOLDER . $ftpConfiguration->ftpConfigurationName->name . \DIRECTORY_SEPARATOR;
        $trackingTodoFolder = self::TRACKING_GOOGLE_FOLDER . self::GOOGLE_FOLDER_TODO;
        $monthFolderPath = $this->getMonthFolderPath();

        $storageFiles = $this->googleStorageFilesystem->listKeys($trackingTodoFolder);

        foreach ($storageFiles as $storageFile) {
            if (preg_match("#$this->inventoryCsvRemoteName(.*?)\.CSV#is", (string) $storageFile)) {
                $this->insertTracking($storageFile);
            }

            $this->googleStorageFilesystem->moveAndReplace(
                $storageFile,
                $trackingFolder . $monthFolderPath . basename((string) $storageFile)
            );
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function setToGoogleCloud(string $localPath, string $googleRootFolder): void
    {
        try {
            $this->googleStorageFilesystem->upload($localPath, $googleRootFolder);
        } catch (\Exception $e) {
            $message = (new Email())
                ->subject('ERREUR envoie Tracking vers Google Cloud' . $googleRootFolder)
                ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                ->to('dev@tediber.com')
                ->text(
                    sprintf('Problème de dépôt de fichier sur storage Cloud%serreur de type: %s', $localPath, $e->getMessage())
                );

            $this->mailer->send($message);
        }
    }

    /**
     * @throws FtpConfigurationNotFoundException
     */
    private function getFtpConfigurationForSpidy(): FtpConfiguration
    {
        return $this->ftpConfigurationRegistry->getFtpConfigurationByName(FtpConfigurationName::SPIDY);
    }

    private function getMonthFolderPath(): string
    {
        return \date('Y') . \DIRECTORY_SEPARATOR . \date('F') . \DIRECTORY_SEPARATOR;
    }

    private function insertTracking(string $trackingFile): void
    {
        $trackingDatas = $this->googleStorageFilesystem->readTemporaryFile($trackingFile);
        $trackingDatas = $this->exportCsv->readCsv($trackingDatas, ';');

        $links = [];
        $ordersNbr = [];
        foreach ($trackingDatas as $data) {
            if ('DET' === $data[0]) {
                $ordersNbr[$data[self::CSV_ORDER_NUMBER]] = $data[self::CSV_ORDER_NUMBER];

                $trackingValues = [
                    'link' => trim((string) $data[self::CSV_TRACKING_URL]),
                    'number' => !in_array(trim((string) $data[self::CSV_TRACKING_URL]), ['', null]) ? $data[self::CSV_TRACKING_NUMBER] : null,
                ];

                if ((str_contains((string) $data[self::CSV_SHIPPING_METHOD], ShippingMethodInterface::METHOD_VIR) && isset($links[$data[self::CSV_ORDER_NUMBER]]))
                    || (isset($links[$data[self::CSV_ORDER_NUMBER]])
                        && in_array($trackingValues, $links[$data[self::CSV_ORDER_NUMBER]]))) {
                    continue;
                }
                $links[$data[self::CSV_ORDER_NUMBER]][] = $trackingValues;
            }
        }

        if (!empty($links)) {
            $orders = $this->orderRepository->findAllLikeNumber(array_keys($links));
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    $this->cliBasedChannelContext->setChannel($order->getChannel());
                    $orderNumber = $order->getNumber();
                    $number = ltrim((string) $orderNumber, '0');
                    $this->assignTrackingUrl->process($order->getTokenValue(), $links['TED' . $number]);
                    $this->orderManager->flush();
                    $this->orderManager->clear();
                }
            }
        }

        if (!empty($ordersNbr)) {
            $orders = $this->orderRepository->findAllLikeNumber(array_keys($ordersNbr));
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    $customer = $order->getCustomer();

                    $this->dripBatchFulfilled->addOrder($order);
                    $this->dripBatchFulfilled->addCustomer($customer);
                }
            }

            $this->dripBatchFulfilled->sendBatchCustomer();
            $this->dripBatchFulfilled->sendBatchOrder();
        }
    }
}
