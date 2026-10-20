<?php

declare(strict_types=1);

namespace App\Channable\Controller;

use App\Channable\Entity\ChannableFileLog;
use App\Channable\Exporter\FileStreamExporter;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/channable", name="app__channable_")
 */
final class FetchFileAction extends AbstractController
{
    public function __construct(
        private readonly RepositoryInterface $channableFileLogRepository,
        private readonly FileStreamExporter $fileStreamExporter
    ) {
    }

    /**
     * @Route("/export", name="export")
     */
    public function __invoke(): Response
    {
        /**
         * @var ChannableFileLog $file
         */
        $file = $this->channableFileLogRepository->findOneBy(['state' => ChannableFileLog::STATE_CURRENT]);
        $response = new Response($this->fileStreamExporter->export($file->getId()));
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=sample.csv');

        $file->setLastTransmission(new \DateTime())
            ->incrementTransmissionCount();
        $this->channableFileLogRepository->add($file);

        return $response;
    }
}
