<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\ProductGoogleRssInterface;
use App\Doctrine\Enum\ProductBundleType;
use Sylius\Component\Core\Model\ProductInterface as BaseProductInterface;
use Sylius\Component\Product\Model\ProductAssociationInterface;

interface ProductInterface extends BaseProductInterface
{
    public const PRODUCT_MATTRESS = 'mattress';
    public const PRODUCT_PELOTE_MATTRESS = 'pelote-mattress';
    public const PRODUCT_BED_FRAME = 'bed-frame';
    public const PRODUCT_KID_STORAGE_BED_FRAME = 'kid-storage-bed-frame';
    public const PRODUCT_STORAGE_BED_FRAME = 'storage-bed-frame';
    public const PRODUCT_BED_HEAD = 'bed-head';

    public function getWeighting(): ?float;

    public function setWeighting(float $weighting): void;

    public function getProductGoogleRss(): ?ProductGoogleRssInterface;

    public function setProductGoogleRss(?ProductGoogleRssInterface $productGoogleRss): void;

    public function hasBundle(): bool;

    public function getWarranty(): ?int;

    public function setWarranty(int $warranty): void;

    public function isEmptyOrContainsBundle(): bool;

    public function isNotEmptyAndDoesNotContainBundle(): bool;

    public function setCanGenerateBundle(bool $canGenerateBundle): void;

    public function getCanGenerateBundle(): bool;

    public function getJoinStoryLink(): ?string;

    public function setJoinStoryLink(?string $joinStoryLink): Product;

    public function getNameAndCode();

    public function getAssociationByType(string $associationTypeCode): ?ProductAssociationInterface;

    public function getBundleType(): ?ProductBundleType;

    public function setBundleType(?ProductBundleType $bundleType): void;

    public function getCrossSellProducts(): array;
}
