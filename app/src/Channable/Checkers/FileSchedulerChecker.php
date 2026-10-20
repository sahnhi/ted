<?php

declare(strict_types=1);

namespace App\Channable\Checkers;

use App\Channable\Entity\ChannableFileLog;
use App\Channable\Exception\Checker\AlreadyProcessingFileException;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class FileSchedulerChecker
{
    public function __construct(
        private readonly RepositoryInterface $channableFileLogRepository
    ) {
    }

    public function check(): bool
    {
        if ($this->hasFileBeenGenerated()) {
            throw new AlreadyProcessingFileException();
        }

        return true;
    }

    private function hasFileBeenGenerated(): bool
    {
        return null !== $this->channableFileLogRepository->findOneBy(['state' => ChannableFileLog::STATE_PROCESSING]);
    }
}
