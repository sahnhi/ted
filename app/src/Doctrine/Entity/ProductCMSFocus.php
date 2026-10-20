<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Entity]
#[ORM\Table(name: 'app_product_cms_focus')]
class ProductCMSFocus implements ResourceInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: 'productCMSFocus')]
    private Product $product;

    #[ORM\OneToMany(mappedBy: 'productCMSFocus', targetEntity: ProductCMSFocusDetails::class, cascade: ['persist', 'remove'])]
    private Collection $productCMSFocusDetails;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
        $this->productCMSFocusDetails = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getTitle(?string $locale = null): ?string
    {
        /**
         * @var ProductCMSFocusTranslations $trans
         */
        $trans = $this->getTranslation($locale);

        return $trans->getTitle();
    }

    public function setTitle(?string $title, ?string $locale = null): self
    {
        /**
         * @var ProductCMSFocusTranslations $trans
         */
        $trans = $this->getTranslation($locale);

        $trans->setTitle($title);

        return $this;
    }

    protected function createTranslation(): ProductCMSFocusTranslations
    {
        return new ProductCMSFocusTranslations();
    }

    public function getProductCMSFocusDetails(): Collection
    {
        return $this->productCMSFocusDetails;
    }

    public function addProductCMSFocusDetail(ProductCMSFocusDetails $productCMSFocusDetail): self
    {
        if (!$this->productCMSFocusDetails->contains($productCMSFocusDetail)) {
            $this->productCMSFocusDetails->add($productCMSFocusDetail);
            $productCMSFocusDetail->setProductCMSFocus($this);
        }

        return $this;
    }

    public function removeProductCMSFocusDetail(ProductCMSFocusDetails $productCMSFocusDetail): self
    {
        if ($this->productCMSFocusDetails->contains($productCMSFocusDetail)) {
            $this->productCMSFocusDetails->removeElement($productCMSFocusDetail);
        }

        return $this;
    }
}
