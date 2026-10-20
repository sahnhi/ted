<?php

declare(strict_types=1);

namespace App\Channable\Message;

final class ExportProductRow
{
    public function __construct(
        private readonly int $channableFileLogId,
        private readonly int $productRowId,
    ) {
    }

    public function getChannableFileLogId(): int
    {
        return $this->channableFileLogId;
    }

    public function getProductRowId(): int
    {
        return $this->productRowId;
    }
}
