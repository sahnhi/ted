<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductVariant;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_shipping_stock')]
#[ORM\Entity]
class ShippingStock implements ResourceInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'warehouse', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Warehouse::class, inversedBy: 'shippingStock')]
    private ?Warehouse $warehouse = null;

    #[ORM\JoinColumn(name: 'product_variant', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductVariant::class, inversedBy: 'shippingStock')]
    private ?ProductVariant $productVariant = null;

    #[ORM\Column(name: 'shipping_date', type: 'datetime', nullable: true)]
    private ?\DateTime $shippingDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(Warehouse $warehouse): void
    {
        $this->warehouse = $warehouse;
    }

    public function getProductVariant(): ?ProductVariant
    {
        return $this->productVariant;
    }

    public function setProductVariant(ProductVariant $productVariant): void
    {
        $productVariant->addShippingStock($this);
        $this->productVariant = $productVariant;
    }

    public function getShippingDate(): ?\DateTime
    {
        return $this->shippingDate;
    }

    public function setShippingDate(\DateTime $shippingDate): void
    {
        $this->shippingDate = $shippingDate;
    }
}
