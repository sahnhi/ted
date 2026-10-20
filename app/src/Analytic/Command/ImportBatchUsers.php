<?php

declare(strict_types=1);

namespace App\Analytic\Command;

final class ImportBatchUsers
{
    public const BATCH_SIZE = 10;

    public function __construct(
        private readonly int $offset,
        private readonly int $numberOfUsersToImport
    ) {
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getNumberOfUsersToImport(): int
    {
        return $this->numberOfUsersToImport;
    }
}
