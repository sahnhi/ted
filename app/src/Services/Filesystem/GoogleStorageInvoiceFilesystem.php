<?php

namespace App\Services\Filesystem;

final class GoogleStorageInvoiceFilesystem extends GoogleStorageFilesystemAbstract
{
    public function upload(string $path, string $key): bool
    {
        $year = substr($key, 1, 4);
        $month = substr($key, 5, 2);

        $this->filesystemStorage->write(
            $year . '/' . $month . '/' . basename($path),
            file_get_contents($path),
            true
        );

        return true;
    }
}
