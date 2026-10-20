<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Doctrine\Entity\ExportAccountingData as EntityExportAccountingData;
use App\Exporter\ExportAccountingDataExporter;
use App\Message\ExportAccountingData;
use App\Services\Filesystem\GoogleStorageExportFileSystem;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExportAccountDataHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly RepositoryInterface $exportAccountingDataRepository,
        private readonly ExportAccountingDataExporter $exportAccountingDataExporter,
        private readonly GoogleStorageExportFileSystem $googleStorageFileSystem,
    ) {
    }

    public function __invoke(ExportAccountingData $message): void
    {
        $exportAccountingData = $this->exportAccountingDataRepository->find($message->getId());
        $data = $this->exportAccountingDataExporter->fetchDataByDateRange($exportAccountingData);
        $filepath = $this->saveFile($message, $data);
        $exportAccountingData->setFilePath($filepath);
        $exportAccountingData->setStatus(EntityExportAccountingData::STATUS_COMPLETED);
        $this->exportAccountingDataRepository->add($exportAccountingData);
    }

    private function saveFile(ExportAccountingData $message, string $data): string
    {
        $fileRelativePath = 'export_accounting_data/' . $message->getId() . '.csv';

        $tmpPath = rtrim(sys_get_temp_dir(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $message->getId() . '.csv';
        if (!file_put_contents($tmpPath, $data)) {
            throw new \RuntimeException(sprintf('Could not create file "%s"!', $tmpPath));
        }

        $this->googleStorageFileSystem->upload($tmpPath, $fileRelativePath);

        unlink($tmpPath);

        return $fileRelativePath;
    }
}
