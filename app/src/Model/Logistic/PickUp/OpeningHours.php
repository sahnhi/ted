<?php

declare(strict_types=1);

namespace App\Model\Logistic\PickUp;

use Webmozart\Assert\Assert;

class OpeningHours
{
    /**
     * This variable follows the PHP DateTime character format (w), thus:
     * 0 = Sunday
     * 1 = Monday
     * 2 = Tuesday
     * 3 = Wednesday
     * 4 = Thursday
     * 5 = Friday
     * 6 = Saturday
     *
     * @see https://www.php.net/manual/en/datetime.format.php
     */
    private int $day;
    private string $openTime;
    private string $closeTime;

    public function getDay(): int
    {
        return $this->day;
    }

    public function setDay(int $day): self
    {
        Assert::lessThanEq($day, 6, 'Day must be less than or equal to 6, refer to PHP DateTime character format (w)');
        $this->day = $day;

        return $this;
    }

    public function getOpenTime(): string
    {
        return $this->openTime;
    }

    public function setOpenTime(string $openTime): self
    {
        $this->openTime = $openTime;

        return $this;
    }

    public function getCloseTime(): string
    {
        return $this->closeTime;
    }

    public function setCloseTime(string $closeTime): self
    {
        $this->closeTime = $closeTime;

        return $this;
    }
}
