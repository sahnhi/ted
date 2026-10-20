<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_shipping_method_category_service')]
class ShippingMethodCategoryService implements ResourceInterface, CodeAwareInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int|null $id = null;

    #[ORM\Column(name: 'code', type: Types::STRING)]
    private string|null $code = null;

    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $enabled = false;

    #[ORM\Column(name: 'included', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $included = false;

    #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
    #[ORM\JoinColumn(name: 'product_variant_id', referencedColumnName: 'id')]
    private ProductVariant $productVariant;

    #[ORM\ManyToOne(targetEntity: ShippingMethodCategory::class, inversedBy: 'services')]
    #[ORM\JoinColumn(name: 'shipping_method_category_id', referencedColumnName: 'id', nullable: false)]
    private ShippingMethodCategory|null $shippingMethodCategory = null;

    #[ORM\JoinTable(name: 'app_shipping_method_category_service_restricted_product')]
    #[ORM\JoinColumn(name: 'shipping_method_category_service_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Product::class)]
    private Collection $restrictedProducts;

    public function __construct()
    {
        $this->initializeTranslationsCollection();

        $this->restrictedProducts = new ArrayCollection();
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getCode(): string|null
    {
        return $this->code;
    }

    public function setCode(string|null $code): void
    {
        $this->code = $code;
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

    public function isIncluded(): bool
    {
        return $this->included;
    }

    public function setIncluded(bool $included): self
    {
        $this->included = $included;

        return $this;
    }

    public function getProductVariant(): ProductVariant
    {
        return $this->productVariant;
    }

    public function setProductVariant(ProductVariant $productVariant): self
    {
        $this->productVariant = $productVariant;

        return $this;
    }

    public function getShippingMethodCategory(): ShippingMethodCategory|null
    {
        return $this->shippingMethodCategory;
    }

    public function setShippingMethodCategory(ShippingMethodCategory|null $shippingMethodCategory): self
    {
        $this->shippingMethodCategory = $shippingMethodCategory;

        return $this;
    }

    public function getPriceForChannel(ChannelInterface $channel): int|null
    {
        return $this->productVariant->getChannelPricingForChannel($channel)?->getPrice();
    }

    public function addRestrictedProduct(Product $product): self
    {
        if (!$this->restrictedProducts->contains($product)) {
            $this->restrictedProducts->add($product);
        }

        return $this;
    }

    public function removeRestrictedProduct(Product $product): self
    {
        if ($this->restrictedProducts->contains($product)) {
            $this->restrictedProducts->removeElement($product);
        }

        return $this;
    }

    /**
     * @return Collection<Product>
     */
    public function getRestrictedProducts(): Collection
    {
        return $this->restrictedProducts;
    }

    public function getTranslation(?string $locale = null): ShippingMethodCategoryServiceTranslation
    {
        /** @var ShippingMethodCategoryServiceTranslation $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    public function getName(?string $locale = null): string
    {
        return $this->getTranslation($locale)->getName();
    }

    protected function createTranslation(): TranslationInterface
    {
        return new ShippingMethodCategoryServiceTranslation();
    }
}
