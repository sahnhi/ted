<?php

declare(strict_types=1);

namespace App\Model\Logistic\PickUp;

use App\Doctrine\Entity\Sylius\OrderInterface;

class SearchableAddress
{
    public function __construct(
        private readonly string $zipCode,
        private readonly ?string $address,
        private readonly string $city,
        private readonly string $countryCode,
        private readonly \DateTimeImmutable $shippingDate,
        private readonly ?OrderInterface $order,
        private readonly iterable $products,
    ) {
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getAddress(): string
    {
        return $this->address ?? '';
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountryCode(): string
    {
        return mb_strtoupper($this->countryCode);
    }

    public function getShippingDate(): \DateTimeImmutable
    {
        return $this->shippingDate;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function getProducts(): iterable
    {
        return $this->products;
    }
}
