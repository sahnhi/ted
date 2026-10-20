<?php

declare(strict_types=1);

namespace App\Processor\ProductFeedFile;

interface ProductFeedFileImporterInterface
{
    public function import(string $path, string $author): void;
}
