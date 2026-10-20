<?php

declare(strict_types=1);

namespace App\Calendar\ViewModels;

final class DeliveryTimes
{
    private array $deliveryTimes = [];

    public function addTime(DeliveryTime $deliveryTime): self
    {
        $this->deliveryTimes[] = $deliveryTime;

        return $this;
    }

    /** @return CalendarSlot[] */
    public function getDeliveryTimes(): array
    {
        return $this->deliveryTimes;
    }
}
