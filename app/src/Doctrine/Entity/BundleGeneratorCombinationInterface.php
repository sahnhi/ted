<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductOptionInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValueInterface;
use Doctrine\Common\Collections\Collection;

interface BundleGeneratorCombinationInterface
{
    public function getId(): int;

    public function getOptionValues(): Collection;

    public function isAlreadyExists(): bool;

    public function setAlreadyExists(bool $alreadyExists): void;

    public function isWillBeGenerated(): bool;

    public function setWillBeGenerated(bool $willBeGenerated): void;

    public function getCode(): ?string;

    public function setCode(?string $code): void;

    public function getBundleGenerator(): ?BundleGeneratorInterface;

    public function setBundleGenerator(?BundleGeneratorInterface $bundleGenerator): void;

    public function getVariants(): Collection;

    public function addOptionValue(ProductOptionValueInterface $productOptionValue): void;

    public function removeOptionValue(ProductOptionValueInterface $productOptionValue): void;

    public function addVariant(BundleGeneratorVariantInterface $variant): void;

    public function removeVariant(BundleGeneratorVariantInterface $variant): void;

    public function getValueForOption(ProductOptionInterface $option): ?ProductOptionValueInterface;

    public function getVariantFromProduct(BundleGeneratorProductInterface $product): ?BundleGeneratorVariantInterface;
}
