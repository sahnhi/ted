<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductOptionInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValueInterface;

interface BundleGeneratorOptionInterface
{
    public function getId(): int;

    public function getProductOption(): ProductOptionInterface;

    public function setProductOption(ProductOptionInterface $productOption): void;

    public function getProductOptionValue(): ?ProductOptionValueInterface;

    public function setProductOptionValue(?ProductOptionValueInterface $productOptionValue): void;

    public function getBundleGenerator(): BundleGeneratorInterface;

    public function setBundleGenerator(BundleGeneratorInterface $bundleGenerator): void;

    public function getPosition(): ?int;

    public function setPosition(?int $position): void;

    public function isForcedValue(): bool;

    public function setForcedValue(bool $forcedValue): void;

    public function isNeedsUnderscore(): bool;

    public function setNeedsUnderscore(bool $needsUnderscore): void;
}
