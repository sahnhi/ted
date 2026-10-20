<?php

declare(strict_types=1);

namespace App\Analytic\Model;

final class OrderItem
{
    private string $brand = 'Tediber';

    public function __construct(
        private readonly string $externalId,
        private readonly string $name,
        private readonly string $productExternalId,
        private readonly int $price,
        private readonly int $quantity,
        private readonly string $category,
        private readonly string $sku
    ) {
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'name' => $this->name,
            'product_external_id' => $this->productExternalId,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'category' => $this->category,
            'sku' => $this->sku,
            'brand' => $this->brand,
        ];
    }
}
