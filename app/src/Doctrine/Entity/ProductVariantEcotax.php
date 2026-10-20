<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductVariant;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_product_variant_ecotax')]
#[ORM\Entity]
class ProductVariantEcotax implements ResourceInterface
{
    public const MANUFACTURER_NOTPAID = 0;
    public const MANUFACTURER_PAID = 1;
    public const MANUFACTURER_RETURN = 2;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'ecotax_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Ecotax::class, inversedBy: 'productVariantEcotax')]
    private ?Ecotax $ecotax = null;

    #[ORM\Column(name: 'quantity', type: 'integer', nullable: false)]
    private ?int $quantity = null;

    #[ORM\Column(name: 'manufacturerPaid', type: 'integer', nullable: false)]
    private int $manufacturerPaid = self::MANUFACTURER_NOTPAID;

    #[ORM\JoinColumn(name: 'variant_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductVariant::class, inversedBy: 'productVariantEcotax')]
    private $variant;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEcotax(): ?Ecotax
    {
        return $this->ecotax;
    }

    public function setEcotax(Ecotax $ecotax): void
    {
        $this->ecotax = $ecotax;
    }

    public function getVariant(): ?ProductVariant
    {
        return $this->variant;
    }

    public function setVariant(ProductVariant $variant): void
    {
        $this->variant = $variant;
    }

    public function getManufacturerPaid(): int
    {
        return $this->manufacturerPaid;
    }

    public function setManufacturerPaid(int $manufacturerPaid): void
    {
        $this->manufacturerPaid = $manufacturerPaid;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
