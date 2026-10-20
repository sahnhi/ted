<?php

declare(strict_types=1);

namespace App\Channable\Message;

final class ExportFile
{
    public function __construct(
        private readonly int $channableFileLogId,
    ) {
    }

    public function getChannableFileLogId(): int
    {
        return $this->channableFileLogId;
    }
}
