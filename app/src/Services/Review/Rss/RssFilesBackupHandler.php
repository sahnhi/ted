<?php

declare(strict_types=1);

namespace App\Services\Review\Rss;

use App\Services\Filesystem\GoogleStorageRssReviewFilesystem;
use App\Services\Sentry\ReportingInterface;
use Psr\Log\LoggerInterface;
use Sentry;

final class RssFilesBackupHandler implements RssFilesBackupHandlerInterface
{
    public const GOOGLE_RSS_BACKUP_FOLDER = 'old-reviews/';
    public const GOOGLE_RSS_TMP_BACKUP_FOLDER = 'tmp-old-reviews/';

    public function __construct(
        private readonly GoogleStorageRssReviewFilesystem $googleStorageFilesystem,
        private readonly LoggerInterface $googleshoppingLogger,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function handle(): void
    {
        $this->googleshoppingLogger->info('[Google Shopping] Backup : START', []);

        try {
            $this->createNewFolder();
            $this->backup();
            $this->clean();
        } catch (\Exception $exception) {
            $this->googleshoppingLogger->error('[Google Shopping] Backup : FAILED', [
                'error' => $exception->getMessage(),
            ]);

            $this->reporting->reportIssue(
                $exception,
                Sentry\Severity::error()
            );
        }

        $this->googleshoppingLogger->info('[Google Shopping] Backup : END', []);
    }

    private function createNewFolder(): void
    {
        $tmpFolder = RssZipFileGenerator::CLOUD_REVIEW_RSS_ZIP_PATH . self::GOOGLE_RSS_TMP_BACKUP_FOLDER;

        if (!$this->googleStorageFilesystem->exists($tmpFolder)) {
            $this->googleStorageFilesystem->mkdir($tmpFolder);
        }
    }

    private function backup(): void
    {
        $tmpFolder = RssZipFileGenerator::CLOUD_REVIEW_RSS_ZIP_PATH . self::GOOGLE_RSS_TMP_BACKUP_FOLDER;

        if (!$this->googleStorageFilesystem->exists($tmpFolder)) {
            throw new RssReviewBackupFilesException();
        }

        $keys = $this->googleStorageFilesystem->listKeys(RssZipFileGenerator::CLOUD_REVIEW_RSS_ZIP_PATH);

        foreach ($keys as $key) {
            $pattern = "/.*(\/"
                . str_replace('/', '', self::GOOGLE_RSS_BACKUP_FOLDER)
                . "\/|\/"
                . str_replace('/', '', self::GOOGLE_RSS_TMP_BACKUP_FOLDER)
                . "\/).*/";
            if (preg_match($pattern, (string) $key)) {
                continue;
            }

            $filename = $this->googleStorageFilesystem->getFilename($key);

            if ($this->googleStorageFilesystem->exists($tmpFolder . $filename)) {
                $this->googleStorageFilesystem->delete($tmpFolder . $filename);
            }

            $this->googleStorageFilesystem->move($key, $tmpFolder . $filename);
        }
    }

    private function clean(): void
    {
        $tmpFolder = RssZipFileGenerator::CLOUD_REVIEW_RSS_ZIP_PATH . self::GOOGLE_RSS_TMP_BACKUP_FOLDER;
        $backupFolder = RssZipFileGenerator::CLOUD_REVIEW_RSS_ZIP_PATH . self::GOOGLE_RSS_BACKUP_FOLDER;

        $files = $this->googleStorageFilesystem->listKeys($backupFolder);

        foreach ($files as $file) {
            $this->googleStorageFilesystem->delete($file);
        }

        // Can't rename so we need to recreate the backupFolder
        $this->googleStorageFilesystem->mkdir($backupFolder);

        $files = $this->googleStorageFilesystem->listKeys($tmpFolder);

        foreach ($files as $file) {
            $filename = $this->googleStorageFilesystem->getFilename($file);
            if (!empty($filename)) {
                $this->googleStorageFilesystem->move($file, $backupFolder . $filename);
            }
        }

        $this->googleStorageFilesystem->delete($tmpFolder);
    }
}
