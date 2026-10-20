<?php

declare(strict_types=1);

namespace App\Services\ZipArchive;

class Zipper
{
    public static function create(
        array $filePaths,
        string $destinationZipPath,
        bool $deleteFileAfterZip = false
    ): void {
        if (empty($filePaths)) {
            return;
        }

        if (file_exists($destinationZipPath)) {
            unlink($destinationZipPath);
        }

        $zip = new \ZipArchive();
        $zip->open($destinationZipPath, \ZipArchive::CREATE);

        foreach ($filePaths as $filePath) {
            $zip->addFile($filePath, basename((string) $filePath));
        }

        $zip->close();

        if ($deleteFileAfterZip) {
            foreach ($filePaths as $filePath) {
                unlink($filePath);
            }
        }
    }
}
