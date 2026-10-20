<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Doctrine\Entity\Sylius\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Entity]
#[ORM\Table(name: 'app_product_cms_sizing_guide')]
#[ApiResource(
    shortName: 'SizingGuide',
    routePrefix: '/shop',
    collectionOperations: ['get'],
    itemOperations: ['get'],
    normalizationContext: ['groups' => ['shop:read:sizing_guide']],
)]
#[ApiFilter(SearchFilter::class, properties: ['products.code' => 'exact'])]
class ProductCMSSizingGuide implements ResourceInterface, TranslatableInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationCollection;
    }

    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $code;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    #[ORM\OneToMany(targetEntity: ProductCMSSizingGuideItem::class, mappedBy: 'productCMSSizingGuide', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'product_cms_sizing_guide_id', referencedColumnName: 'id')]
    private Collection $productCMSSizingGuideItems;

    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'productCMSSizingGuide')]
    private Collection $products;

    public function __construct()
    {
        $this->initializeTranslationCollection();
        $this->productCMSSizingGuideItems = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->getTrans()->getTitle();
    }

    public function setTitle(string $title): self
    {
        $this->getTrans()->setTitle($title);

        return $this;
    }

    public function getSubTitle(): ?string
    {
        return $this->getTrans()->getSubTitle();
    }

    public function setSubTitle(?string $subTitle): self
    {
        $this->getTrans()->setSubTitle($subTitle);

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->getTrans()->getLink();
    }

    public function setLink(?string $link): self
    {
        $this->getTrans()->setLink($link);

        return $this;
    }

    public function getProductCMSSizingGuideItems(): Collection
    {
        return $this->productCMSSizingGuideItems;
    }

    public function hasProductCMSSizingGuideItem(ProductCMSSizingGuideItem $productCMSSizingGuideItem): bool
    {
        return $this->productCMSSizingGuideItems->contains($productCMSSizingGuideItem);
    }

    public function addProductCMSSizingGuideItem(ProductCMSSizingGuideItem $productCMSSizingGuideItem): self
    {
        if (!$this->hasProductCMSSizingGuideItem($productCMSSizingGuideItem)) {
            $this->productCMSSizingGuideItems->add($productCMSSizingGuideItem);
            $productCMSSizingGuideItem->setProductCMSSizingGuide($this);
        }

        return $this;
    }

    public function removeProductCMSSizingGuideItem(ProductCMSSizingGuideItem $productCMSSizingGuideItem): self
    {
        if ($this->hasProductCMSSizingGuideItem($productCMSSizingGuideItem)) {
            $this->productCMSSizingGuideItems->removeElement($productCMSSizingGuideItem);
        }

        return $this;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->hasProduct($product)) {
            $this->products->add($product);
            $product->setProductCMSSizingGuide($this);
        }

        return $this;
    }

    public function hasProduct(Product $product): bool
    {
        return $this->products->contains($product);
    }

    public function removeProduct(Product $product): self
    {
        if ($this->hasProduct($product)) {
            $product->setProductCMSSizingGuide(null);
            $this->products->removeElement($product);
        }

        return $this;
    }

    public function getTrans(): ProductCMSSizingGuideTranslation
    {
        /** @var ProductCMSSizingGuideTranslation $translation */
        $translation = $this->getTranslation();

        return $translation;
    }

    protected function createTranslation(): ProductCMSSizingGuideTranslation
    {
        return new ProductCMSSizingGuideTranslation();
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }
}
