<?php

declare(strict_types=1);

namespace App\Channable\Exporter;

use App\Channable\Message\ExportFile;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ChannableExporter implements ChannableExporterInterface
{
    public function __construct(
        private readonly RepositoryInterface $channableFileLogRepository,
        private readonly MessageBusInterface $bus,
        private readonly FactoryInterface $channableFileLogFactory,
    ) {
    }

    public function export(): void
    {
        $channableFileLog = $this->channableFileLogFactory->createNew();
        $this->channableFileLogRepository->add($channableFileLog);
        $this->bus->dispatch(new ExportFile($channableFileLog->getId()));
    }
}
