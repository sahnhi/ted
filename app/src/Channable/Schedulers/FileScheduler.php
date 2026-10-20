<?php

declare(strict_types=1);

namespace App\Channable\Schedulers;

use App\Channable\Checkers\FileSchedulerChecker;
use App\Channable\Entity\ChannableFileLog;
use App\Channable\Message\ExportFile;
use App\Channable\Repository\ChannableFileLogRepository;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class FileScheduler
{
    private const FILE_HISTORY_LIMIT = 5;

    public function __construct(
        private readonly ChannableFileLogRepository $channableFileLogRepository,
        private readonly FileSchedulerChecker $fileSchedulerChecker,
        private readonly FactoryInterface $channableFileLogFactory,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function run(): void
    {
        $this->garbageCollect();
        $this->fileSchedulerChecker->check();

        $channableFileLog = $this->createFileLog();

        $this->queueFile($channableFileLog);
        $this->clear();
        $this->activateFileLog($channableFileLog);
        $this->disableFiles($channableFileLog);
    }

    public function disableFiles(?ChannableFileLog $currentChannableFile): void
    {
        foreach ($this->channableFileLogRepository->findBy(['state' => ChannableFileLog::STATE_CURRENT]) as $channableFileLog) {
            if ($channableFileLog !== $currentChannableFile) {
                $channableFileLog->setState(ChannableFileLog::STATE_OLD);
                $this->channableFileLogRepository->add($channableFileLog);
            }
        }
    }

    private function createFileLog(): ChannableFileLog
    {
        $channableFileLog = $this->channableFileLogFactory->createNew();
        $channableFileLog->setState(ChannableFileLog::STATE_PROCESSING);
        $this->channableFileLogRepository->add($channableFileLog);

        return $channableFileLog;
    }

    private function queueFile(ChannableFileLog $channableFileLog): void
    {
        $exportFile = new ExportFile($channableFileLog->getId());
        $this->messageBus->dispatch($exportFile);
    }

    private function garbageCollect(): void
    {
        foreach ($this->channableFileLogRepository->findBy(['state' => ChannableFileLog::STATE_PROCESSING]) as $channableFileLog) {
            if ($channableFileLog->getCreatedAt() < new \DateTime('-10 seconds')) {
                $channableFileLog->setState(ChannableFileLog::STATE_ERROR);
                $this->channableFileLogRepository->add($channableFileLog);
            }
        }
    }

    private function clear(): void
    {
        $i = 0;
        $rows = $this->channableFileLogRepository->findAllOrderedDesc();
        foreach ($rows as $channableFileLog) {
            if ($i++ >= self::FILE_HISTORY_LIMIT) {
                $this->channableFileLogRepository->remove($channableFileLog);
            }
        }
    }

    private function activateFileLog(ChannableFileLog $channableFileLog): void
    {
        $channableFileLog->setState(ChannableFileLog::STATE_CURRENT);
        $this->channableFileLogRepository->add($channableFileLog);
    }
}
