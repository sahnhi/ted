<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Repository\ProductFeedFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Entity(repositoryClass: ProductFeedFileRepository::class)]
#[ORM\Table(name: 'app_product_feed_file')]
class ProductFeedFile implements ResourceInterface, TranslatableInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationCollection;
    }
    use TimestampableEntity;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'google_id', type: 'string')]
    private string $googleId;

    #[ORM\OneToOne(targetEntity: ProductVariant::class)]
    #[ORM\JoinColumn(name: 'product_variant_id')]
    private ProductVariantInterface $productVariant;

    #[ORM\OneToMany(targetEntity: ProductFeedFileAdditionalImages::class, mappedBy: 'productFeedFile', cascade: ['all'], orphanRemoval: true)]
    private Collection $additionalImages;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    #[ORM\Column(name: 'main_image', type: 'string')]
    protected string $mainImage;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    protected $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    protected $updatedAt;

    /**
     * @param string $locale
     */
    public function __construct($locale = 'fr_FR')
    {
        $this->initializeTranslationCollection();
        $this->additionalImages = new ArrayCollection();
        $this->setCurrentLocale($locale);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setGoogleId(string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getGoogleId(): string
    {
        return $this->googleId;
    }

    public function getProductVariant(): ProductVariantInterface
    {
        return $this->productVariant;
    }

    public function setProductVariant(ProductVariantInterface $productVariant): self
    {
        $this->productVariant = $productVariant;

        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getAdditionalImages(): Collection
    {
        return $this->additionalImages;
    }

    public function hasAdditionalImages(): bool
    {
        return !$this->additionalImages->isEmpty();
    }

    public function addAdditionalImage(string $additionalImage): self
    {
        if ($this->hasAdditionalImage($additionalImage)) {
            return $this;
        }

        $obj = new ProductFeedFileAdditionalImages();
        $obj->setProductFeedFile($this)
            ->setUrl($additionalImage);
        $this->additionalImages->add($obj);

        return $this;
    }

    public function hasAdditionalImage(string $additionalImage): bool
    {
        return $this->additionalImages->contains($additionalImage);
    }

    public function clearAdditionalImages(): self
    {
        $this->additionalImages->clear();

        return $this;
    }

    public function getMainImage(): string
    {
        return $this->mainImage;
    }

    public function setMainImage(string $mainImage): self
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    public function setTitle(string $title): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        $trans->setTitle($title);

        return $this;
    }

    public function getTitle(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getTitle();
    }

    public function setDescription(string $description): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setDescription($description);

        return $this;
    }

    public function getDescription(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getDescription();
    }

    public function setGoogleCategory(string $googleCategory): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setGoogleCategory($googleCategory);

        return $this;
    }

    public function getGoogleCategory(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getGoogleCategory();
    }

    public function getProductType(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getProductType();
    }

    public function setProductType(string $productType): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setProductType($productType);

        return $this;
    }

    public function getMaterial(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getMaterial();
    }

    public function setMaterial(string $material): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setMaterial($material);

        return $this;
    }

    public function getUrl(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getUrl();
    }

    public function setUrl(string $url): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setUrl($url);

        return $this;
    }

    public function getObjectState(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getObjectState();
    }

    public function setObjectState(string $objectState): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setObjectState($objectState);

        return $this;
    }

    public function getPromotionIdentifier(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getPromotionIdentifier();
    }

    public function setPromotionIdentifier(string $promotionIdentifier): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setPromotionIdentifier($promotionIdentifier);

        return $this;
    }

    public function getGroupIdentifier(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getGroupIdentifier();
    }

    public function setGroupIdentifier(string $groupIdentifier): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setGroupIdentifier($groupIdentifier);

        return $this;
    }

    public function getSizingSystem(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getSizingSystem();
    }

    public function setSizingSystem(string $sizes): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setSizingSystem($sizes);

        return $this;
    }

    public function getComposition(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getComposition();
    }

    public function setComposition(string $composition): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setComposition($composition);

        return $this;
    }

    public function getTags(): string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getTags();
    }

    public function setTags(?string $tags): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setTags($tags);

        return $this;
    }

    public function clearTags(): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setTags('');

        return $this;
    }

    public function getColor(): ?string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getColor();
    }

    public function setColor(?string $color): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setColor($color);

        return $this;
    }

    public function getHighlight(): ?string
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();

        return $trans->getHighlight();
    }

    public function setHighlight(?string $highlights): self
    {
        /**
         * @var ProductFeedFileTranslation $trans
         */
        $trans = $this->getTranslation();
        $trans->setHighlight($highlights);

        return $this;
    }

    public function createTranslation(): ProductFeedFileTranslation
    {
        return new ProductFeedFileTranslation();
    }
}
