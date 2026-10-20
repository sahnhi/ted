<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOptionInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValueInterface;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;

interface BundleGeneratorInterface extends ResourceInterface
{
    public function getProduct(): ProductInterface;

    public function setProduct(ProductInterface $product): void;

    public function getOptions(): Collection;

    public function getCombinations(): Collection;

    public function getProducts(): Collection;

    public function getPrefix(): string;

    public function setPrefix(string $prefix): void;

    public function getSuffix(): string;

    public function setSuffix(string $suffix): void;

    public function getNbProducts(): int;

    public function setNbProducts(int $nbProducts): void;

    public function addOption(BundleGeneratorOptionInterface $generatorOption): void;

    public function removeOption(BundleGeneratorOptionInterface $generatorOption): void;

    public function addCombination(BundleGeneratorCombinationInterface $combination): void;

    public function removeCombination(BundleGeneratorCombinationInterface $combination): void;

    public function addProduct(BundleGeneratorProductInterface $generatorProduct): void;

    public function removeProduct(BundleGeneratorProductInterface $generatorProduct): void;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function setCreatedAt(?\DateTimeInterface $createdAt): void;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void;

    public function isGenerated(): bool;

    public function setIsGenerated(bool $isGenerated): void;

    public function getGeneratorOptionForOption(ProductOptionInterface $option): ?BundleGeneratorOptionInterface;

    public function getGeneratorCombinationForOptionValues(array $optionValues): ?BundleGeneratorCombinationInterface;

    public function getCombinationsToGenerate(): Collection;

    public function hasCombinationToGenerate(): bool;

    public function getFormatedValueForCode(ProductOptionValueInterface $optionValue): string;

    public function generateCodeForCombination(BundleGeneratorCombinationInterface $combinationConfiguration): string;

    public function getShippingCategory(): ?ShippingCategoryInterface;

    public function setShippingCategory(?ShippingCategoryInterface $shippingCategory): void;
}
