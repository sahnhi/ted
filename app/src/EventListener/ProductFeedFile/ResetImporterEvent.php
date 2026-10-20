<?php

declare(strict_types=1);

namespace App\EventListener\ProductFeedFile;

use App\Event\ProductFeedFile\PreValidatorEvent;
use App\Services\ProductFeedFile\Importer\GarbageCollectorServiceInterface;

final class ResetImporterEvent
{
    public function __construct(
        private readonly GarbageCollectorServiceInterface $garbageCollector
    ) {
    }

    public function __invoke(PreValidatorEvent $event): void
    {
        $this->garbageCollector->clear();
    }
}
