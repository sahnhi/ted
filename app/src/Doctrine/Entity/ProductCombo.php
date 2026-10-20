<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Repository\ProductComboRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_product_combo')]
#[ORM\Entity(repositoryClass: ProductComboRepository::class)]
class ProductCombo implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\Column(name: 'quantity', type: 'float', nullable: false)]
    private ?float $quantity = null;

    #[ORM\JoinColumn(name: 'product_variant_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Sylius\ProductVariant::class)]
    private ?ProductVariant $productVariant = null;

    #[ORM\JoinColumn(name: 'product_variant_logistic_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Sylius\ProductVariant::class, fetch: 'EXTRA_LAZY')]
    private ?ProductVariant $productVariantLogistic = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getProductVariant(): ?ProductVariant
    {
        return $this->productVariant;
    }

    public function setProductVariant(ProductVariant $productVariant): void
    {
        $this->productVariant = $productVariant;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getProductVariantLogistic(): ?ProductVariant
    {
        return $this->productVariantLogistic;
    }

    public function setProductVariantLogistic(ProductVariant $productVariantLogistic): void
    {
        $this->productVariantLogistic = $productVariantLogistic;
    }
}
