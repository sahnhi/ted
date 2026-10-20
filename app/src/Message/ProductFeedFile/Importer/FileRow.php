<?php

declare(strict_types=1);

namespace App\Message\ProductFeedFile\Importer;

use App\Model\ProductFeedFile\Row;

final class FileRow
{
    public function __construct(
        private readonly Row $rowData,
        private readonly int $productFeedId,
        private readonly bool $isLastRow,
    ) {
    }

    public function getRowData(): Row
    {
        return $this->rowData;
    }

    public function getProductFeedId(): int
    {
        return $this->productFeedId;
    }

    public function isLastRow(): bool
    {
        return $this->isLastRow;
    }
}
