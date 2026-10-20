<?php

declare(strict_types=1);

namespace App\Processor\ProductFeedFile\Importer;

use App\Doctrine\Entity\ProductFeedFileImporter;

interface RowImporterInterface
{
    public function import(ProductFeedFileImporter $productFeedFileImporter, iterable $row): void;
}
