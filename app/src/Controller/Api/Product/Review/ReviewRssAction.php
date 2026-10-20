<?php

namespace App\Controller\Api\Product\Review;

use App\Services\Filesystem\GoogleStorageRssReviewFilesystem;
use App\Services\Review\Rss\RssZipFileGenerator;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ReviewRssAction
{
    public function __construct(
        private readonly ViewHandlerInterface $viewHandler,
        private readonly GoogleStorageRssReviewFilesystem $googleStorageFilesystem
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            if (!$this->googleStorageFilesystem->exists(RssZipFileGenerator::CLOUD_REVIEW_RSS_ZIP_PATH . RssZipFileGenerator::CLOUD_REVIEW_RSS_ZIP_FILE)) {
                return $this->viewHandler->handle(View::create([]));
            }
            $temporaryPath = $this->googleStorageFilesystem->readTemporaryFile(RssZipFileGenerator::CLOUD_REVIEW_RSS_ZIP_PATH . RssZipFileGenerator::CLOUD_REVIEW_RSS_ZIP_FILE);

            return new BinaryFileResponse($temporaryPath);
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
