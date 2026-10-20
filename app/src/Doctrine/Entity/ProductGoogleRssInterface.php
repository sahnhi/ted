<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;

interface ProductGoogleRssInterface
{
    public function getSku(): ?string;

    public function setSku(?string $sku): void;

    public function getGtin(): ?string;

    public function setGtin(?string $gtin): void;

    public function getMpn(): ?string;

    public function setMpn(?string $mpn): void;

    public function getUrlReview(): ?string;

    public function setUrlReview(?string $urlReview): void;

    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): void;

    public function getId(): ?int;

    public function getVariant(): ?ProductVariantInterface;

    public function setVariant(?ProductVariantInterface $variant): void;

    public function getProductUrl(): ?string;

    public function setProductUrl(?string $productUrl): void;
}
