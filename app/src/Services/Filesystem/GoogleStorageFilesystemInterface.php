<?php

namespace App\Services\Filesystem;

interface GoogleStorageFilesystemInterface
{
    public function upload(string $path, string $key): bool;

    public function download(string $key, string $path): ?string;

    public function read(string $key): ?string;

    public function readTemporaryFile(string $key): string;

    public function listKeys(string $prefix): array;

    public function delete(string $key): bool;

    public function move(string $keyFrom, string $keyTo): bool;

    public function exists(string $key): bool;

    public function retrieve(string $key);

    public function mkdir(string $key): bool;
}
