<?php

namespace App\Services\Transmission\Entrepot\Spidy;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Edeliv\Client\EdelivClient;
use App\Enum\FtpConfigurationName;
use App\Exception\FtpConfigurationNotFoundException;
use App\Registry\FtpConfigurationRegistryInterface;
use App\Services\Admin\Export\ExportCsv;
use App\Services\Filesystem\GoogleStorageTransmissionFilesystem;
use App\Services\Sentry\ReportingInterface;
use App\Services\Transmission\Transporteur\EDelivService;

final class Cancel extends AbstractSpidy
{
    public const SPIDY_CANCEL_COLS = ['company', 'warehouse', 'spidy reserved', 'Order number', 'line number', 'spidy reserved', 'Error email'];
    public const SPIDY_CANCEL_FILE_NAME = 'SHIPCANTED';

    public function __construct(
        string $projectDir,
        FtpConfigurationRegistryInterface $ftpConfigurationRegistry,
        ExportCsv $exportCsv,
        private readonly EDelivService $edelivService,
        private readonly string $urbantzApiUrl,
        GoogleStorageTransmissionFilesystem $googleStorageTransmissionFilesystem,
        ReportingInterface $reporting
    ) {
        parent::__construct(
            $projectDir,
            $ftpConfigurationRegistry,
            $exportCsv,
            $googleStorageTransmissionFilesystem,
            $reporting
        );
    }

    /**
     * @throws FtpConfigurationNotFoundException
     */
    public function cancel(Order $order, $sendToQ77 = true): void
    {
        $channelCode = $order->getChannel()->getCode();
        if ('return' === $channelCode || true !== $order->getErpForwarded()) {
            return;
        }

        if ($sendToQ77) {
            $this->writeCsv($order);
        }

        $shipment = $order->getShipments()->first();
        $shippingMethod = $shipment->getMethod();
        $number = $this->getTrimedNumber($order);

        if (str_contains((string) $shippingMethod->getCode(), ShippingMethodInterface::METHOD_EDELIV)) {
            $url = $this->urbantzApiUrl . str_replace('{taskId}', $number, EdelivClient::URBANTZ_CANCEL_URL);

            $this->backupPayload(json_encode(['url' => $url, 'payload' => []]), AbstractSpidy::CANCEL_FOLDER, $order->getNumber(), $shippingMethod->getCode());
            $this->edelivService->callUrbantz($number);
        }
    }

    /**
     * @throws FtpConfigurationNotFoundException
     */
    public function writeCsv(Order $order): void
    {
        [$sftp, $ftpConfiguration] = $this->connect(FtpConfigurationName::SPIDY);

        $monthFolderPath = sprintf('/%s%s', \date('Y/'), \date('F'));
        $dirPath = sprintf('%s%s/', $ftpConfiguration->ftpConfigurationName->value, $monthFolderPath);
        $localPath = sprintf('%scancel/%s', $this->privateFolder, $dirPath);
        $fileName = sprintf('%s%s.csv', self::SPIDY_CANCEL_FILE_NAME, $this->getTrimedNumber($order));

        $data = $this->writeDatas($order);

        $this->mkdir($localPath);
        $this->exportCsv->createCsv($localFile = $localPath . $fileName, $data, true, false);

        $this->put($sftp, sprintf('%s/%s', $ftpConfiguration->directory, $fileName), $localFile, 'Cancel file upload failed', $order->getNumber());
        unlink($localFile);
    }

    private function writeDatas(Order $order): array
    {
        return [
            self::SPIDY_CANCEL_COLS,
            ['SQ7', 'SQ7', '', 'TED' . ltrim((string) $order->getNumber(), '0'), '', '', 'dev@tediber.com'],
        ];
    }

    private function getTrimedNumber(Order $order): string
    {
        return ltrim((string) $order->getNumber(), 0);
    }
}
