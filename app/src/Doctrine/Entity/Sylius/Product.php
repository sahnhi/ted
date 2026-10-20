<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\ProductCMSClaimsTrait;
use App\Doctrine\Entity\ProductCMSFaqTrait;
use App\Doctrine\Entity\ProductCMSFocusTrait;
use App\Doctrine\Entity\ProductCMSTagsTrait;
use App\Doctrine\Entity\ProductCMSTechnicalInfosTrait;
use App\Doctrine\Entity\ProductCMSTrait;
use App\Doctrine\Entity\ProductGoogleRss;
use App\Doctrine\Entity\ProductGoogleRssInterface;
use App\Doctrine\Entity\ProductReviewQuestion;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\RtbInfosProductTrait;
use App\Doctrine\Entity\Trait\ProductCMS\ProductCMSSizingGuideTrait;
use App\Doctrine\Enum\ProductBundleType;
use App\Doctrine\Repository\Sylius\ProductRepository;
use App\Services\Channel\ChannelCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusGiftCardPlugin\Model\ProductInterface as SetonoSyliusGiftCardProductInterface;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductAssociation;
use Sylius\Component\Product\Model\ProductAssociationInterface;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct implements ProductInterface, SetonoSyliusGiftCardProductInterface
{
    use ProductCMSTagsTrait;
    use RtbInfosProductTrait;
    use ProductCMSTrait;
    use ProductCMSFocusTrait;
    use ProductCMSFaqTrait;
    use ProductCMSTechnicalInfosTrait {
        ProductCMSTechnicalInfosTrait::__construct as private initializeTechnicalInfosCollection;
    }

    use ProductCMSClaimsTrait {
        ProductCMSClaimsTrait::__construct as private initializeClaimsCollection;
    }

    use ProductCMSSizingGuideTrait;

    public const TRUST_PILOT_REVIEW_CODE = 'reviews_trustpilot';
    public const BRAND_REVIEW_CODE = 'TEDCARNET';

    #[ORM\Column(name: 'weighting', type: 'float', nullable: false, options: ['default' => '1'])]
    private float $weighting = 1;

    #[ORM\Column(name: 'gift_card', type: 'boolean')]
    private bool $giftCard = false;

    #[ORM\Column(name: 'review_requested', type: 'boolean')]
    private bool $reviewRequested = false;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductReviewQuestion::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $productReviewQuestions;

    #[ORM\OneToOne(inversedBy: 'product', targetEntity: ProductGoogleRss::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'productgooglerss_id', referencedColumnName: 'id', nullable: true)]
    private ?ProductGoogleRssInterface $productGoogleRss = null;

    #[ORM\Column(name: 'warranty', type: 'integer', nullable: true)]
    private ?int $warranty = null;

    #[ORM\Column(name: 'can_generate_bundle', type: 'boolean')]
    private bool $canGenerateBundle = false;

    #[ORM\ManyToMany(targetEntity: Reason::class, mappedBy: 'products')]
    private ?Collection $reasons = null;

    #[ORM\Column(name: 'meta_data', type: 'ordered_array', nullable: true)]
    private ?array $metaData;

    #[ORM\Column(name: 'join_story_link', type: 'text', nullable: true)]
    private ?string $joinStoryLink = null;

    #[ORM\Column(name: 'bundle_type', type: 'text', nullable: true, enumType: ProductBundleType::class)]
    private ?ProductBundleType $bundleType = null;

    #[ORM\Column(name: 'template', type: 'string', nullable: true)]
    private ?string $template = null;

    #[ORM\Column(name: 'quantity_selector', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $quantitySelector = false;

    public function __construct()
    {
        parent::__construct();

        $this->metaData = [];
        $this->productReviewQuestions = new ArrayCollection();
        $this->initializeClaimsCollection();
        $this->initializeTechnicalInfosCollection();
    }

    public function getBundleType(): ?ProductBundleType
    {
        return $this->bundleType;
    }

    public function getBundleTypeValue(): ?string
    {
        return $this->bundleType?->value;
    }

    public function setBundleType(?ProductBundleType $bundleType): void
    {
        $this->bundleType = $bundleType;
    }

    public function getProductGoogleRss(): ?ProductGoogleRssInterface
    {
        return $this->productGoogleRss;
    }

    public function setProductGoogleRss(?ProductGoogleRssInterface $productGoogleRss): void
    {
        $this->productGoogleRss = $productGoogleRss;
        $productGoogleRss->setProduct($this);
    }

    public function getWeighting(): ?float
    {
        return $this->weighting;
    }

    public function setWeighting(float $weighting): void
    {
        $this->weighting = $weighting;
    }

    public function isGiftCard(): bool
    {
        return $this->giftCard;
    }

    public function setGiftCard(bool $isGiftCard): void
    {
        $this->giftCard = $isGiftCard;
    }

    public function isReviewRequested(): bool
    {
        return $this->reviewRequested;
    }

    public function setReviewRequested(bool $reviewRequested): void
    {
        $this->reviewRequested = $reviewRequested;
    }

    /**
     * @return Collection<ProductReviewQuestion>
     */
    public function getProductReviewQuestions(): Collection
    {
        return $this->productReviewQuestions;
    }

    /**
     * @return Collection<ProductReviewQuestion>
     */
    public function getEnabledProductReviewQuestions(): Collection
    {
        return $this->getProductReviewQuestions()
            ->filter(static fn (ProductReviewQuestion $productReviewQuestion): bool => $productReviewQuestion->isEnabled() ?? false);
    }

    public function addProductReviewQuestion(ProductReviewQuestion $productReviewQuestions): void
    {
        if (!$this->productReviewQuestions->contains($productReviewQuestions)) {
            $this->productReviewQuestions->add($productReviewQuestions);
            $productReviewQuestions->setProduct($this);
        }
    }

    public function removeProductReviewQuestion(ProductReviewQuestion $productReviewQuestions): void
    {
        if ($this->productReviewQuestions->contains($productReviewQuestions)) {
            $this->productReviewQuestions->removeElement($productReviewQuestions);
            $productReviewQuestions->setProduct(null);
        }
    }

    public function hasBundle(): bool
    {
        /**
         * @var ProductVariantInterface $variant
         */
        foreach ($this->variants as $variant) {
            if ($variant->isBundle()) {
                return true;
            }
        }

        return false;
    }

    public function getWarranty(): ?int
    {
        return $this->warranty;
    }

    public function setWarranty(int $warranty): void
    {
        $this->warranty = $warranty;
    }

    public function getReasons(): ?Collection
    {
        return $this->reasons;
    }

    public function setReasons(?Collection $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    public function setMetaData(?array $metaData): void
    {
        $this->metaData = $metaData;
    }

    public function isEmptyOrContainsBundle(): bool
    {
        return $this->canGenerateBundle && ($this->variants->isEmpty() || $this->hasBundle());
    }

    public function isNotEmptyAndDoesNotContainBundle(): bool
    {
        return !$this->variants->isEmpty() && !$this->hasBundle();
    }

    public function getCanGenerateBundle(): bool
    {
        return $this->canGenerateBundle;
    }

    public function setCanGenerateBundle(bool $canGenerateBundle): void
    {
        $this->canGenerateBundle = $canGenerateBundle;
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

    public function getNameAndCode(): string
    {
        return $this->getName() . ' (' . $this->getCode() . ')';
    }

    public function getAssociationByType(string $associationTypeCode): ?ProductAssociationInterface
    {
        /**
         * @var ProductAssociation|false $association
         */
        $association = $this->associations->filter(
            static fn (ProductAssociation $productAssociation): bool => $productAssociation->getType()?->getCode() === $associationTypeCode
        )->first();

        if (false === $association) {
            return null;
        }

        return $association;
    }

    public function getReviewsCount(): int
    {
        return $this->getReviews()->count();
    }

    public function getEnabledOptions(): Collection
    {
        $enabledOptions = new ArrayCollection();
        /**
         * @var ProductOption $option
         */
        foreach ($this->getOptions() as $option) {
            if ($option->getEnabledValues()->isEmpty()) {
                continue;
            }

            $enabledOptions->add($option);
        }

        return $enabledOptions;
    }

    public function setOptions(Collection $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getCrossSellProducts(): array
    {
        $crossSellProducts = [];
        /** @var Collection<ProductAssociation> $associations */
        $associations = $this->getAssociations();

        foreach ($associations as $association) {
            if (ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING === $association->getType()?->getCode()) {
                foreach ($association->getAssociatedProducts() as $associatedProduct) {
                    $crossSellProducts[] = $associatedProduct->getCode();
                }
            }
        }

        return $crossSellProducts;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getEnabledVariantsWithOptions(): array
    {
        $variants = [];

        /**
         * @var ProductVariantInterface $variant
         */
        foreach ($this->getVariants() as $variant) {
            $var = [];
            $enabledOptionValues = $variant->getOptionValues()
                ->filter(static fn (ProductOptionValue $value): bool => $value->getEnabled());
            foreach ($enabledOptionValues as $option) {
                $var[$variant->getCode()]['optionValues'][] = $option->getCode();
                foreach ($variant->getChannelPricings() as $pricing) {
                    if (ChannelCode::CHANNEL_WEB === $pricing->getChannelCode()) {
                        $var[$variant->getCode()]['price'] = $pricing->getPrice();
                        $var[$variant->getCode()]['originalPrice'] = $pricing->getOriginalPrice() ?? $pricing->getPrice();
                        break;
                    }
                }
            }
            if ([] !== $var) {
                $variants[] = $var;
            }
        }

        return $variants;
    }

    public function hasQuantitySelector(): bool
    {
        return $this->quantitySelector;
    }

    public function setQuantitySelector(bool $quantitySelector): void
    {
        $this->quantitySelector = $quantitySelector;
    }
}
