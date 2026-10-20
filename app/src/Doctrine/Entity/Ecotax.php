<?php

namespace App\Doctrine\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_ecotax')]
#[ORM\Entity]
class Ecotax implements ResourceInterface, \Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'code', type: 'string', nullable: false)]
    private ?string $code = null;

    #[ORM\Column(name: 'amount_ht', type: 'integer', nullable: true)]
    private ?int $amountHT = null;

    #[ORM\Column(name: 'amount_ttc', type: 'integer', nullable: true)]
    private ?int $amountTTC = null;

    #[ORM\OneToMany(mappedBy: 'ecotax', targetEntity: ProductVariantEcotax::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Collection $productVariantEcotax;

    public function __construct()
    {
        $this->productVariantEcotax = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getCode() . ' - ' . ($this->getAmountTTC() / 100) . ' €';
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getAmountTTC(): ?int
    {
        return $this->amountTTC;
    }

    public function setAmountTTC(?int $amountTTC): void
    {
        $this->amountTTC = $amountTTC;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmountHT(): ?int
    {
        return $this->amountHT;
    }

    public function setAmountHT(?int $amountHT): void
    {
        $this->amountHT = $amountHT;
    }

    public function getProductVariantEcotax(): ArrayCollection|Collection|null
    {
        return $this->productVariantEcotax;
    }

    public function addProductVariantEcotax(ProductVariantEcotax $productVariantEcotax): void
    {
        if (!$this->productVariantEcotax->contains($productVariantEcotax)) {
            $this->productVariantEcotax[] = $productVariantEcotax;
            $productVariantEcotax->setEcotax($this);
        }
    }

    public function removeProductVariantEcotax(ProductVariantEcotax $productVariantEcotax): void
    {
        if ($this->productVariantEcotax->contains($productVariantEcotax)) {
            $this->productVariantEcotax->removeElement($productVariantEcotax);
        }
    }
}
