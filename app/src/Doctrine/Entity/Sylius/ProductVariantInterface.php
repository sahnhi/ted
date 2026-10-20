<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\BundleAssociation;
use App\Doctrine\Entity\BundleAssociationInterface;
use App\Doctrine\Entity\Pack;
use App\Doctrine\Entity\ProductGoogleRssInterface;
use App\Doctrine\Entity\ProductVariantEcotax;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ProductVariantInterface as BaseProductVariantInterface;

interface ProductVariantInterface extends BaseProductVariantInterface
{
    public const BUNDLE_PREFIX = 'bundle';

    public function getEcotax(): ?int;

    public function setEcotax(int $ecotax): void;

    public function getPacks(): ?Collection;

    public function setPacks(Collection $packs): void;

    public function addPack(Pack $pack): void;

    public function removePack(Pack $pack): void;

    /**
     * @return Collection<BundleAssociationInterface>
     */
    public function getBundleAssociations(): Collection;

    public function addBundleAssociation(BundleAssociationInterface $bundleAssociation): void;

    public function removeBundleAssociation(BundleAssociationInterface $bundleAssociation): void;

    public function getBundleAssociation(string $code): ?BundleAssociation;

    public function isBundle(): bool;

    public function getProductGoogleRss(): ?ProductGoogleRssInterface;

    public function setProductGoogleRss(?ProductGoogleRssInterface $productGoogleRss): void;

    public function getProductVariantEcotax(): ArrayCollection|Collection|null;

    public function addProductVariantEcotax(ProductVariantEcotax $productVariantEcotax): void;

    public function removeProductVariantEcotax(ProductVariantEcotax $productVariantEcotax): void;

    public function getShippingStock(): ?Collection;

    public function getExcludeFromPromotions(): bool;

    public function setExcludeFromPromotions(bool $excludeFromPromotions): void;

    public function getJoinStoryLink(): ?string;

    public function setJoinStoryLink(?string $joinStoryLink): ProductVariant;

    public function getShippingCategoryClaims(): string|null;

    public function setEan13(?int $ean13): self;

    public function getEan13(): ?int;
}
