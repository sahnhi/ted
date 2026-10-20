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
use Sylius\Component\Resource\Model\TranslationInterface;

#[ORM\Table(name: 'app_product_cms_technical_infos')]
#[ORM\Entity]
class ProductCMSTechnicalInfos implements ResourceInterface, TranslatableInterface
{
    use TimestampableEntity;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToMany(targetEntity: ProductCMSTechnicalDetails::class, mappedBy: 'productCMSTechnicalInfos', cascade: ['all'], orphanRemoval: true)]
    private Collection $productCMSTechnicalDetails;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productCMSTechnicalInfos')]
    private Product $product;

    public function __construct()
    {
        $this->productCMSTechnicalDetails = new ArrayCollection();
        $this->initializeTranslationsCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        /**
         * @var ProductCMSTechnicalInfosTranslations $trans
         */
        $trans = $this->getTranslation();

        return $trans->getTitle();
    }

    public function setTitle(string $title): self
    {
        /**
         * @var ProductCMSTechnicalInfosTranslations $trans
         */
        $trans = $this->getTranslation();
        $trans->setTitle($title);

        return $this;
    }

    public function addProductCMSTechnicalDetail(ProductCMSTechnicalDetails $productCMSTechnicalDetails): self
    {
        if (!$this->productCMSTechnicalDetails->contains($productCMSTechnicalDetails)) {
            $this->productCMSTechnicalDetails->add($productCMSTechnicalDetails);
            $productCMSTechnicalDetails->setProductCMSTechnicalInfos($this);
        }

        return $this;
    }

    public function removeProductCMSTechnicalDetail(ProductCMSTechnicalDetails $productCMSTechnicalDetails): self
    {
        if ($this->productCMSTechnicalDetails->contains($productCMSTechnicalDetails)) {
            $this->productCMSTechnicalDetails->removeElement($productCMSTechnicalDetails);
        }

        return $this;
    }

    public function getProductCMSTechnicalDetails(): Collection
    {
        return $this->productCMSTechnicalDetails;
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

    protected function createTranslation(): TranslationInterface
    {
        return new ProductCMSTechnicalInfosTranslations();
    }
}
