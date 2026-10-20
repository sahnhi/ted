<?php

declare(strict_types=1);

namespace App\Analytic\Model;

final class OrderItemList
{
    public function __construct(
        private array $orderItems
    ) {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function addToList(OrderItem $orderItem): self
    {
        $orderItems = $this->orderItems;
        $orderItems[] = $orderItem;

        return new self($orderItems);
    }

    public function toArray(): array
    {
        return array_map(static fn (OrderItem $orderItem): array => $orderItem->toArray(), $this->orderItems);
    }
}
