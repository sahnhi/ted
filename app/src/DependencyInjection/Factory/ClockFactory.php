<?php

declare(strict_types=1);

namespace App\DependencyInjection\Factory;

use Lcobucci\Clock\FrozenClock;

class ClockFactory
{
    public static function createFrozenClock(string $dateTime): FrozenClock
    {
        return new FrozenClock(new \DateTimeImmutable($dateTime));
    }
}
