<?php

declare(strict_types=1);

namespace App\Services\Cron\Review\Preview;

use App\Message\PreviewRss\PreviewRssZip;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class PreviewRssZipPublisher implements PreviewRssZipPublisherInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $googleshoppingLogger
    ) {
    }

    public function publish(): void
    {
        $this->googleshoppingLogger->info('[Google Shopping] Zip published', []);
        $this->bus->dispatch(new PreviewRssZip());
    }
}
