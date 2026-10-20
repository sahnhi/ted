<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\BundleAssociation;
use App\Doctrine\Entity\BundleAssociationInterface;
use App\Doctrine\Entity\Pack;
use App\Doctrine\Entity\ProductGoogleRss;
use App\Doctrine\Entity\ProductGoogleRssInterface;
use App\Doctrine\Entity\ProductVariantEcotax;
use App\Doctrine\Entity\ShippingStock;
use App\Doctrine\Entity\Trait\ProductFeedFile\ProductFeedFileTrait;
use App\Doctrine\Entity\Trait\SatellitePage\ProductVariantSatellitePageTrait;
use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;

/**
 * @method Product|null          getProduct()
 * @method ShippingCategory|null getShippingCategory()
 */
#[ORM\Table(name: 'sylius_product_variant')]
#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
class ProductVariant extends BaseProductVariant implements ProductVariantInterface
{
    use ProductFeedFileTrait;

    use ProductVariantSatellitePageTrait;

    #[ORM\Column(name: 'ecotax', type: 'integer', nullable: false)]
    private int $ecotax = 0;

    #[ORM\Column(name: 'exclude_from_promotions', type: 'boolean', nullable: false)]
    private bool $excludeFromPromotions = false;

    #[ORM\Column(name: 'join_story_link', type: 'text', nullable: true)]
    private ?string $joinStoryLink = null;

    #[ORM\OneToOne(inversedBy: 'variant', targetEntity: ProductGoogleRss::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'productgooglerss_id', referencedColumnName: 'id', nullable: true)]
    private ?ProductGoogleRssInterface $productGoogleRss = null;

    #[ORM\OneToMany(targetEntity: ShippingStock::class, mappedBy: 'productVariant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $shippingStock;

    #[ORM\OneToMany(targetEntity: ProductVariantEcotax::class, mappedBy: 'variant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Collection $productVariantEcotax = null;

    #[ORM\OneToMany(targetEntity: BundleAssociation::class, mappedBy: 'bundle', cascade: ['all'], orphanRemoval: true)]
    private ?Collection $bundleAssociations = null;

    #[ORM\JoinTable(name: 'sylius_product_variant_packs')]
    #[ORM\JoinColumn(name: 'product_variant_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'pack_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Pack::class, inversedBy: 'variants')]
    private ?Collection $packs;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?int $ean13 = null;

    #[ORM\JoinTable(name: 'sylius_product_variant_addons')]
    #[ORM\JoinColumn(name: 'product_variant_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'pack_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Pack::class, inversedBy: 'addons')]
    private Collection $addons;

    public function __construct()
    {
        parent::__construct();

        $this->packs = new ArrayCollection();
        $this->addons = new ArrayCollection();
        $this->shippingStock = new ArrayCollection();
        $this->productVariantEcotax = new ArrayCollection();
        $this->bundleAssociations = new ArrayCollection();
    }

    public function getEcotax(): ?int
    {
        return $this->ecotax;
    }

    public function setEcotax(int $ecotax): void
    {
        $this->ecotax = $ecotax;
    }

    public function getPacks(): ?Collection
    {
        return $this->packs;
    }

    public function setPacks(Collection $packs): void
    {
        $this->packs = $packs;
    }

    public function addPack(Pack $pack): void
    {
        if (!$this->packs->contains($pack)) {
            $this->packs[] = $pack;
        }
    }

    public function removePack(Pack $pack): void
    {
        if ($this->packs->contains($pack)) {
            $this->packs->removeElement($pack);
        }
    }

    public function setOnHand(?int $onHand): void
    {
        $this->onHand = $onHand; // (0 > $onHand) ? 0 : $onHand
    }

    /**
     * @return Collection<ShippingStock>
     */
    public function getShippingStock(): Collection
    {
        return $this->shippingStock;
    }

    public function addShippingStock(ShippingStock $shippingStock): void
    {
        if (!$this->shippingStock->contains($shippingStock)) {
            $this->shippingStock[] = $shippingStock;
            $shippingStock->setProductVariant($this);
        }
    }

    public function removeShippingStock(ShippingStock $shippingStock): void
    {
        if ($this->shippingStock->contains($shippingStock)) {
            $this->shippingStock->removeElement($shippingStock);
        }
    }

    public function getFirstShippingStockDate()
    {
        return !$this->getShippingStock()->isEmpty() && null !== ($date = $this->getShippingStock()->first()->getShippingDate()) ?
            $date :
            null;
    }

    public function getNameAndCode(): string
    {
        return $this->getName() . ' (' . $this->getCode() . ')';
    }

    public function getProductVariantEcotax(): ArrayCollection|Collection|null
    {
        return $this->productVariantEcotax;
    }

    public function addProductVariantEcotax(ProductVariantEcotax $productVariantEcotax): void
    {
        if (!$this->productVariantEcotax->contains($productVariantEcotax)) {
            $this->productVariantEcotax[] = $productVariantEcotax;
            $productVariantEcotax->setVariant($this);
        }
    }

    public function removeProductVariantEcotax(ProductVariantEcotax $productVariantEcotax): void
    {
        if ($this->productVariantEcotax->contains($productVariantEcotax)) {
            $this->productVariantEcotax->removeElement($productVariantEcotax);
        }
    }

    /**
     * @return Collection<BundleAssociation>
     */
    public function getBundleAssociations(): Collection
    {
        return $this->bundleAssociations;
    }

    public function addBundleAssociation(BundleAssociationInterface $bundleAssociation): void
    {
        if (!$this->bundleAssociations->contains($bundleAssociation)) {
            $this->bundleAssociations->add($bundleAssociation);
            $bundleAssociation->setBundle($this);
        }
    }

    public function removeBundleAssociation(BundleAssociationInterface $bundleAssociation): void
    {
        if ($this->bundleAssociations->contains($bundleAssociation)) {
            $this->bundleAssociations->removeElement($bundleAssociation);
        }
    }

    public function getBundleAssociation(string $code): ?BundleAssociation
    {
        foreach ($this->bundleAssociations as $association) {
            if ($association->getVariant()->getCode() === $code) {
                return $association;
            }
        }

        return null;
    }

    public function isBundle(): bool
    {
        return null !== $this->bundleAssociations && !$this->bundleAssociations->isEmpty();
    }

    public function getProductGoogleRss(): ?ProductGoogleRssInterface
    {
        return $this->productGoogleRss;
    }

    public function setProductGoogleRss(?ProductGoogleRssInterface $productGoogleRss): void
    {
        $this->productGoogleRss = $productGoogleRss;
        $productGoogleRss?->setVariant($this);
    }

    public function getExcludeFromPromotions(): bool
    {
        return $this->excludeFromPromotions;
    }

    public function setExcludeFromPromotions(bool $excludeFromPromotions): void
    {
        $this->excludeFromPromotions = $excludeFromPromotions;
    }

    public function getJoinStoryLink(): ?string
    {
        return $this->joinStoryLink;
    }

    public function setJoinStoryLink(?string $joinStoryLink): self
    {
        $this->joinStoryLink = $joinStoryLink;

        return $this;
    }

    public function getEan13(): ?int
    {
        return $this->ean13;
    }

    public function setEan13(?int $ean13): self
    {
        $this->ean13 = $ean13;

        return $this;
    }

    public function getShippingCategoryClaims(): string|null
    {
        return $this->getShippingCategory()?->getTranslation()->getClaims();
    }

    public function getEnabledOptionValues(): Collection
    {
        $enabledOptionValues = $this->getOptionValues()
            ->filter(static fn (ProductOptionValue $value): bool => $value->getEnabled());

        return new ArrayCollection($enabledOptionValues->getValues());
    }

    public function setOptionValues(Collection $optionValues): self
    {
        $this->optionValues = $optionValues;

        return $this;
    }

    public function getAddons(): Collection
    {
        return $this->addons;
    }

    public function addAddon(Pack $pack): void
    {
        if (!$this->addons->contains($pack)) {
            $this->addons->add($pack);
        }
    }

    public function removeAddon(Pack $pack): void
    {
        if ($this->addons->contains($pack)) {
            $this->addons->removeElement($pack);
        }
    }

    public function getProductDefaultName(): ?string
    {
        return $this->product->getName();
    }

    public function getTotalEcotaxes(): ?int
    {
        $totalEcotaxes = 0;

        foreach ($this->productVariantEcotax as $productVariantEcotax) {
            $totalEcotaxes += $productVariantEcotax->getQuantity() * $productVariantEcotax->getEcotax()->getAmountTTC();
        }

        return $totalEcotaxes;
    }

    public function getFullOptionValue(): ?string
    {
        if ($this->optionValues->isEmpty()) {
            return null;
        }

        $optionValues = $this->optionValues->toArray();
        // We sort by options position, the lowest position first
        usort($optionValues, static function (ProductOptionValue $a, ProductOptionValue $b): int {
            if ($a->getOption()?->getPosition() > $b->getOption()?->getPosition()) {
                return 1;
            }

            return -1;
        });

        $values = [];
        foreach ($optionValues as $optionValue) {
            $values[] = $optionValue->getValue();
        }

        return implode(' - ', $values);
    }
}
