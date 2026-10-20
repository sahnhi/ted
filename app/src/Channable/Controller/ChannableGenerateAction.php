<?php

declare(strict_types=1);

namespace App\Channable\Controller;

use App\Channable\Entity\ChannableFileLog;
use App\Channable\Exception\Checker\AlreadyProcessingFileException;
use App\Channable\Exporter\FileStreamExporter;
use App\Channable\Schedulers\FileScheduler;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ChannableGenerateAction extends AbstractController
{
    public function __construct(
        private readonly FileScheduler $fileScheduler,
        private readonly FileStreamExporter $fileStreamExporter,
        private readonly RepositoryInterface $channableFileLogRepository,
    ) {
    }

    public function __invoke(): Response
    {
        try {
            $this->fileScheduler->run();
        } catch (AlreadyProcessingFileException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_channable_file_log_index');
    }

    public function export(int $id): Response
    {
        try {
            $response = new Response($this->fileStreamExporter->export($id));
            $response->headers->set('Content-Encoding', 'UTF-8');
            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', 'attachment; filename=sample.csv');

            return $response;
        } catch (AlreadyProcessingFileException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_channable_file_log_index');
    }

    public function enabled_file(int $id): Response
    {
        $channableFileLog = $this->channableFileLogRepository->find($id);
        $this->fileScheduler->disableFiles($channableFileLog);
        $channableFileLog->setState(ChannableFileLog::STATE_CURRENT);
        $this->channableFileLogRepository->add($channableFileLog);

        return $this->redirectToRoute('app_admin_channable_file_log_index');
    }
}
