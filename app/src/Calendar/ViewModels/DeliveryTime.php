<?php

declare(strict_types=1);

namespace App\Calendar\ViewModels;

final class DeliveryTime
{
    public function __construct(
        private readonly ?int $place,
        private readonly string $providerName,
        private readonly bool $dailyDelivery,
        private readonly string $start_time,
        private readonly string $end_time,
        private readonly ?string $shipping_message
    ) {
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function isDailyDelivery(): bool
    {
        return $this->dailyDelivery;
    }

    public function getStartTime(): string
    {
        return $this->start_time;
    }

    public function getEndTime(): string
    {
        return $this->end_time;
    }

    public function getShippingMessage(): ?string
    {
        return $this->shipping_message;
    }
}
