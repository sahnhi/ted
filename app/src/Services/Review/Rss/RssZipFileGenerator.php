<?php

declare(strict_types=1);

namespace App\Services\Review\Rss;

use App\Services\Filesystem\GoogleStorageRssReviewFilesystem;
use App\Services\ZipArchive\Zipper;
use Psr\Log\LoggerInterface;

final class RssZipFileGenerator implements RssZipFileGeneratorInterface
{
    public const CLOUD_REVIEW_RSS_ZIP_PATH = 'rss_review/';
    public const CLOUD_REVIEW_RSS_ZIP_FILE = 'review.zip';
    private readonly string $reviewReviewRssLocalFolder;

    public function __construct(
        private readonly GoogleStorageRssReviewFilesystem $googleStorageFilesystem,
        string $projectDir,
        private readonly LoggerInterface $googleshoppingLogger
    ) {
        $this->reviewReviewRssLocalFolder = $projectDir . '/src/Resources/private/reviews/';
    }

    public function generate(): void
    {
        $this->googleshoppingLogger->info('[Google Shopping] Zip generation : START', []);

        try {
            $filenamePrefix = RssXmlFileGenerator::FILENAME_PREFIX;
            $files = [];

            $keys = array_filter($this->googleStorageFilesystem->listKeys(self::CLOUD_REVIEW_RSS_ZIP_PATH), function ($key) use ($filenamePrefix): bool {
                $exploded = explode('/', $key);
                $filename = end($exploded);

                return preg_match("/^$filenamePrefix.*\.(xml)$/", $filename)
                    && !preg_match("/.*(\/" . str_replace('/', '', RssFilesBackupHandler::GOOGLE_RSS_BACKUP_FOLDER) .
                    "\/|\/" . str_replace('/', '', RssFilesBackupHandler::GOOGLE_RSS_TMP_BACKUP_FOLDER) . "\/).*/", $key);
            });

            if (empty($keys)) {
                throw new RssReviewXmlFilesNotFoundException();
            }

            foreach ($keys as $key) {
                $files[] = $this->googleStorageFilesystem->readTemporaryFile($key);
            }

            $localZipPath = $this->reviewReviewRssLocalFolder . self::CLOUD_REVIEW_RSS_ZIP_FILE;
            Zipper::create($files, $localZipPath, true);
            $this->googleStorageFilesystem->upload($localZipPath, self::CLOUD_REVIEW_RSS_ZIP_PATH . self::CLOUD_REVIEW_RSS_ZIP_FILE);
        } catch (\Exception $exception) {
            $this->googleshoppingLogger->error('[Google Shopping] Zip generation : FAILED', [
                'error' => $exception->getMessage(),
            ]);
        }

        $this->googleshoppingLogger->info('[Google Shopping] Zip generation : END', []);
    }
}
