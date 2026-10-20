<?php

declare(strict_types=1);

namespace App\Services\ProductFeedFile\Importer;

interface GarbageCollectorServiceInterface
{
    // in seconds
    public const PROCESSING_TOLERANCE_TIME = 300;

    public function clear(int $toleranceTimeInSeconds = self::PROCESSING_TOLERANCE_TIME): void;
}
