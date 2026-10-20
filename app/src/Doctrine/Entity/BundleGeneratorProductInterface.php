<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductInterface;

interface BundleGeneratorProductInterface
{
    public function getId(): int;

    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): void;

    public function getQuantity(): int;

    public function setQuantity(int $quantity): void;

    public function isFree(): bool;

    public function setIsFree(bool $isFree): void;

    public function getBundleGenerator(): ?BundleGeneratorInterface;

    public function setBundleGenerator(?BundleGeneratorInterface $bundleGenerator): void;
}
