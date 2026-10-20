<?php

declare(strict_types=1);

namespace App\Services\Review\Rss;

use App\Services\Cron\Review\Preview\PreviewRssXmlPublisherInterface;
use App\Services\Cron\Review\Preview\PreviewRssZipPublisherInterface;
use App\Services\Filesystem\GoogleStorageRssReviewFilesystem;
use App\Services\Sentry\ReportingInterface;
use App\View\GoogleRssView;
use App\ViewRepository\ProductReviewViewRepositoryInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sentry;

final class RssXmlFileGenerator implements RssXmlFileGeneratorInterface
{
    public const FILENAME_PREFIX = 'reviews-';

    public const GOOGLE_RSS_MAX_RESULT_PER_FILE = 500;
    private readonly string $reviewRssZipFilePath;

    public function __construct(
        private readonly ProductReviewViewRepositoryInterface $productReviewViewRepository,
        private readonly SerializerInterface $serializer,
        string $projectDir,
        private readonly GoogleStorageRssReviewFilesystem $googleStorageFilesystem,
        private readonly RssFilesBackupHandlerInterface $backupHandler,
        private readonly PreviewRssXmlPublisherInterface $rssXmlPublisher,
        private readonly PreviewRssZipPublisherInterface $rssZipPublisher,
        private readonly LoggerInterface $googleshoppingLogger,
        private readonly ReportingInterface $reporting
    ) {
        $this->reviewRssZipFilePath = $projectDir . '/src/Resources/private/reviews/';
    }

    public function generate(int $offset, int $total): void
    {
        $this->googleshoppingLogger->info('[Google Shopping] XML file generation : START', [
            'offset' => $offset,
            'total' => $total,
        ]);

        if (0 === $offset) {
            $this->backupHandler->handle();
        }

        try {
            [$iteration, $googleView] = $this->productReviewViewRepository->getAllActivatedReviews($offset, self::GOOGLE_RSS_MAX_RESULT_PER_FILE);
            $index = $offset + $iteration;

            $this->writeXml($googleView, $index);
        } catch (\Exception $exception) {
            $this->googleshoppingLogger->error('[Google Shopping] XML writing : FAILED', [
                'offset' => $offset,
                'newOffset' => $index ?? null,
                'total' => $total,
                'exception' => $exception->getMessage(),
            ]);

            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                ['rss_review_xml_offset' => (string) $offset]
            );

            return;
        }

        $this->googleshoppingLogger->info('[Google Shopping] XML file generation : END', [
            'offset' => $offset,
            'newOffset' => $index,
            'total' => $total,
        ]);

        if ($index < $total) {
            $this->rssXmlPublisher->publish($index, $total);
        } else {
            $this->rssZipPublisher->publish();
        }
    }

    private function writeXml(GoogleRssView $googleView, int $index): void
    {
        $filename = self::FILENAME_PREFIX . $index . '.xml';

        try {
            $context = SerializationContext::create()->setGroups(['google.rss']);
            $xml = $this->serializer->serialize($googleView, 'xml', $context);

            if (!is_dir($this->reviewRssZipFilePath)) {
                mkdir($this->reviewRssZipFilePath, 0755, true);
            }

            file_put_contents($this->reviewRssZipFilePath . $filename, $xml);
            $this->googleStorageFilesystem->upload($this->reviewRssZipFilePath . $filename, RssZipFileGenerator::CLOUD_REVIEW_RSS_ZIP_PATH . $filename);
        } catch (\Exception $exception) {
            $this->googleshoppingLogger->error('[Google Shopping] XML file generation : FAILED', [
                'filename' => $filename,
                'error' => $exception->getMessage(),
            ]);

            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error(),
                ['rss_review_xml_file' => $filename],
                ['index' => $index]
            );
        }
    }
}
