<?php

declare(strict_types=1);

namespace App\Message\DTO;

final class VariantDTO
{
    public function __construct(
        private readonly string $productName,
        private readonly array $optionsName,
        private readonly string $price,
        private readonly int $quantity
    ) {
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getOptionsDetails(): string
    {
        return implode(' - ', $this->optionsName);
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
