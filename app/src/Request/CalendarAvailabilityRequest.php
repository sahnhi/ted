<?php

namespace App\Request;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;

final class CalendarAvailabilityRequest
{
    private \DateTime $date;

    public function __construct(
        \DateTime|false $date,
        private readonly \DateTime $now,
        private readonly string $countryCode,
        private readonly string $postalCode,
        private readonly string $channelCode,
        private readonly array $products = [],
        private readonly int $limit = 40,
        private readonly bool $withoutStock = false,
        private readonly ?\DateTime $dateLimit = null,
        private bool $calendar = true,
        private readonly Order|null $order = null,
        private readonly string|null $selectedShippingMethodCategoryCode = null,
    ) {
        $this->date = false === $date ? new \DateTime() : $date;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $dateTime): CalendarAvailabilityRequest
    {
        $this->date = $dateTime;

        return $this;
    }

    public function getNow(): \DateTime
    {
        return $this->now;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function isWithoutStock(): bool
    {
        return $this->withoutStock;
    }

    public function getChannelCode(): string
    {
        return $this->channelCode;
    }

    /**
     * @return array<array-key, array{code: string, quantity: int}>
     */
    public function getProducts(): array
    {
        if (null !== $this->order) {
            return $this->formatOrderItems($this->order->getItems());
        }

        return $this->products;
    }

    public function getDateLimit(): ?\DateTime
    {
        return $this->dateLimit;
    }

    public function isCalendar(): bool
    {
        return $this->calendar;
    }

    public function setCalendar(bool $calendar): CalendarAvailabilityRequest
    {
        $this->calendar = $calendar;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function getSelectedShippingMethodCategoryCode(): ?string
    {
        return $this->selectedShippingMethodCategoryCode;
    }

    /**
     * @param iterable<OrderItem> $orderItems
     *
     * @return array{array{code: string, quantity: int}}
     */
    private function formatOrderItems(iterable $orderItems): array
    {
        $formattedOrderItems = [];
        foreach ($orderItems as $orderItem) {
            $formattedOrderItems[] = [
                'code' => $orderItem->getVariant()?->getCode(),
                'quantity' => $orderItem->getQuantity(),
            ];
        }

        return $formattedOrderItems;
    }
}
