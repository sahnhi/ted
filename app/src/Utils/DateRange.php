<?php

declare(strict_types=1);

namespace App\Utils;

use Webmozart\Assert\Assert;

final class DateRange
{
    /** @var \DateTimeImmutable */
    private $from;
    /** @var \DateTimeImmutable */
    private $to;

    public function __construct(\DateTimeImmutable $from, \DateTimeImmutable $to)
    {
        Assert::greaterThan($to, $from);
        $this->from = $from;
        $this->to = $to;
    }

    public function getFrom(): \DateTimeImmutable
    {
        return $this->from;
    }

    public function getTo(): \DateTimeImmutable
    {
        return $this->to;
    }
}
