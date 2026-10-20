<?php

declare(strict_types=1);

namespace App\Calendar\ViewModels;

final class CalendarSlot
{
    public function __construct(
        private readonly DeliveryTimes $times,
        private readonly ?bool $dailyFlag,
        private readonly ?bool $dailyDelivery
    ) {
    }

    public function getTimes(): DeliveryTimes
    {
        return $this->times;
    }

    public function isDailyFlag(): ?bool
    {
        return $this->dailyFlag;
    }

    public function isDailyDelivery(): ?bool
    {
        return $this->dailyDelivery;
    }
}
