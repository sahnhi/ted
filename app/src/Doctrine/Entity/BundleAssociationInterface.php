<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\ResourceInterface;

interface BundleAssociationInterface extends ResourceInterface
{
    public function getBundle(): ProductVariantInterface;

    public function setBundle(ProductVariantInterface $bundle): void;

    public function getVariant(): ?ProductVariantInterface;

    public function setVariant(?ProductVariantInterface $variant): void;

    public function getQuantity(): int;

    public function setQuantity(?int $quantity): void;

    public function getPrices(): Collection;

    public function addPrice(BundleAssociationPriceInterface $price): void;

    public function removePrice(BundleAssociationPriceInterface $price): void;

    public function getPrice(): int;

    public function getPriceAtDate(?\DateTime $date = null): int;

    public function setPrice(int $price): void;

    public function getCurrentPriceObject(): BundleAssociationPriceInterface;

    public function getPriceObjectAtDate(\DateTime $date): BundleAssociationPriceInterface;
}
