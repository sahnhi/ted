<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Doctrine\Repository\ProductFeedFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\AbstractTranslation;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity(repositoryClass: ProductFeedFileRepository::class)]
#[ORM\Table(name: 'app_product_feed_file_translation')]
class ProductFeedFileTranslation extends AbstractTranslation implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(type: 'string')]
    protected string $title;

    #[ORM\Column(type: 'text')]
    protected string $description;

    #[ORM\Column(name: 'google_category', type: 'string')]
    protected string $googleCategory;

    #[ORM\Column(name: 'product_type', type: 'string')]
    protected string $productType;

    #[ORM\Column(type: 'string')]
    protected string $material;

    #[ORM\Column(type: 'string')]
    protected string $url;

    #[ORM\Column(name: 'object_state', type: 'string')]
    protected string $objectState = 'new';

    #[ORM\Column(name: 'promotion_identifier', type: 'string')]
    protected string $promotionIdentifier;

    #[ORM\Column(name: 'group_identifier', type: 'string')]
    protected string $groupIdentifier;

    #[ORM\Column(name: 'sizing_system', type: 'string')]
    protected string $sizingSystem;

    #[ORM\Column(type: 'string')]
    protected string $composition;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $color = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $highlight = null;

    #[ORM\Column(type: 'string')]
    protected ?string $tags = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getGoogleCategory(): string
    {
        return $this->googleCategory;
    }

    public function setGoogleCategory(string $googleCategory): self
    {
        $this->googleCategory = $googleCategory;

        return $this;
    }

    public function getProductType(): string
    {
        return $this->productType;
    }

    public function setProductType(string $productType): self
    {
        $this->productType = $productType;

        return $this;
    }

    public function getMaterial(): string
    {
        return $this->material;
    }

    public function setMaterial(string $material): self
    {
        $this->material = $material;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getObjectState(): string
    {
        return $this->objectState;
    }

    public function setObjectState(string $objectState): self
    {
        $this->objectState = $objectState;

        return $this;
    }

    public function getPromotionIdentifier(): string
    {
        return $this->promotionIdentifier;
    }

    public function setPromotionIdentifier(string $promotionIdentifier): self
    {
        $this->promotionIdentifier = $promotionIdentifier;

        return $this;
    }

    public function getGroupIdentifier(): string
    {
        return $this->groupIdentifier;
    }

    public function setGroupIdentifier(string $groupIdentifier): self
    {
        $this->groupIdentifier = $groupIdentifier;

        return $this;
    }

    public function getSizingSystem(): string
    {
        return $this->sizingSystem;
    }

    public function setSizingSystem(string $sizingSystem): self
    {
        $this->sizingSystem = $sizingSystem;

        return $this;
    }

    public function getComposition(): string
    {
        return $this->composition;
    }

    public function setComposition(string $composition): self
    {
        $this->composition = $composition;

        return $this;
    }

    public function getTags(): string
    {
        $this->tags = rtrim((string) $this->tags, ',');

        return trim($this->tags);
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;
        // dd($this->tags, $this->locale);

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getHighlight(): ?string
    {
        return $this->highlight;
    }

    public function setHighlight(?string $highlight): self
    {
        $this->highlight = $highlight;

        return $this;
    }
}
