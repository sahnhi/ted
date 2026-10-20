<?php

declare(strict_types=1);

namespace App\Generator\Renderer\Model;

final class InvoiceProduct
{
    private function __construct(
        private readonly string $variantName,
        private readonly string $name,
        private readonly string $code,
        private readonly string $unitPrice,
        private int $quantity,
        private readonly float $tva,
        private readonly ?float $weight = null,
        private readonly ?string $htc = null
    ) {
    }

    public static function createWithVariantPriceDividedByNumberOfLogisticVariants(
        string $variantName,
        string $name,
        string $code,
        int $total,
        int $quantity,
        int $variantQuantity,
        float $tva,
        ?float $weight = null,
        ?string $htc = null
    ): self {
        return new self($variantName, $name, $code, (string) ceil($total / $variantQuantity), $quantity, $tva, $weight, $htc);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTotalPrice(): string
    {
        return (string) ((float) $this->unitPrice * (float) $this->quantity);
    }

    public function addQuantity(int $quantityToAdd): void
    {
        $this->quantity += $quantityToAdd;
    }

    public function getVariantName(): string
    {
        return $this->variantName;
    }

    public function getTva(): float
    {
        return $this->tva;
    }

    public function getHtPrice(): float
    {
        return (float) $this->unitPrice * 100 / (100 + $this->tva);
    }

    public function getTotalHtPrice(): float
    {
        return $this->quantity * $this->getHtPrice();
    }

    public function getHtc(): ?string
    {
        return $this->htc;
    }

    public function getWeight(): float
    {
        return $this->weight ?? 0;
    }
}
