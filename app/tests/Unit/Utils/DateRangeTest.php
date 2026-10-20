<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Utils\DateRange;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

final class DateRangeTest extends TestCase
{
    /** @dataProvider providesDateRanges() */
    public function testCreateDateRanges($from, $to): void
    {
        $dateRange = new DateRange($from, $to);

        $this->assertEquals($from, $dateRange->getFrom());
        $this->assertEquals($to, $dateRange->getTo());
    }

    public function providesDateRanges(): \Generator
    {
        yield '1 minute' => [new \DateTimeImmutable('now'), new \DateTimeImmutable('+1 minute')];
        yield '2 hours' => [new \DateTimeImmutable('now'), new \DateTimeImmutable('+2 hours')];
        yield '3 hours, 22 minutes, 14 seconds' => [new \DateTimeImmutable('2020-01-01T16:00:00'), new \DateTimeImmutable('2020-01-01T19:22:14')];
    }

    public function testEnsureToIsGreaterThanFrom(): void
    {
        $from = new \DateTimeImmutable('now');
        $to = new \DateTimeImmutable('-1 minute');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Expected a value greater than DateTimeImmutable: "%s". Got: DateTimeImmutable: "%s"', $from->format(\DateTime::ATOM), $to->format(\DateTime::ATOM)));

        new DateRange($from, $to);
    }
}
