<?php

declare(strict_types=1);

namespace App\Model\Logistic\PickUp;

class PickUpPoints
{
    private array $points = [];

    public function __construct(
        private readonly string $shippingMethodCode,
        private readonly int $shippingMethodId
    ) {
    }

    public function getPoints(): array
    {
        return $this->points;
    }

    public function addPoint(PickUpPoint $point): void
    {
        $this->points[] = $point;
    }

    public function getPoint(int $index): PickUpPoint
    {
        return $this->points[$index];
    }

    public function removePoint(int $index): void
    {
        unset($this->points[$index]);
    }

    public function getShippingMethodCode(): string
    {
        return $this->shippingMethodCode;
    }

    public function getShippingMethodId(): int
    {
        return $this->shippingMethodId;
    }
}
