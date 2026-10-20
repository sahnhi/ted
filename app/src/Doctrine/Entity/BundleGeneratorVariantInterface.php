<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductVariantInterface;

interface BundleGeneratorVariantInterface
{
    public function getId(): int;

    public function getProduct(): BundleGeneratorProductInterface;

    public function setProduct(BundleGeneratorProductInterface $product): void;

    public function getCombination(): BundleGeneratorCombinationInterface;

    public function setCombination(BundleGeneratorCombinationInterface $combination): void;

    public function getVariant(): ?ProductVariantInterface;

    public function setVariant(?ProductVariantInterface $variant): void;
}
