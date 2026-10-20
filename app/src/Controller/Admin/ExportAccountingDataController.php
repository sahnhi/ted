<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Doctrine\Entity\ExportAccountingData;
use App\Services\Filesystem\GoogleStorageExportFileSystem;
use Gaufrette\Exception\FileNotFound;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExportAccountingDataController extends AbstractController
{
    public function __construct(
        private readonly RepositoryInterface $exportAccountingDataRepository,
        private readonly GoogleStorageExportFileSystem $googleStorageExportFileSystem
    ) {
    }

    public function downloadAction(Request $request): Response
    {
        $entityId = (int) $request->get('id');
        $exportAccountingData = $this->exportAccountingDataRepository->find($entityId);

        if (!$exportAccountingData instanceof ExportAccountingData) {
            throw new NotFoundHttpException('Export accounting data not found');
        }

        $relativePathFile = $exportAccountingData->getFilepath();

        if (!$this->googleStorageExportFileSystem->exists($relativePathFile)) {
            throw new FileNotFound('Export accounting file not found');
        }

        $temporaryPath = $this->googleStorageExportFileSystem->readTemporaryFile($relativePathFile);

        return new BinaryFileResponse($temporaryPath, Response::HTTP_OK, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export_accounting_data.csv"',
        ]);
    }
}
