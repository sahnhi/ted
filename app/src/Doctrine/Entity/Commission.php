<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Product;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Table(name: 'sylius_commission')]
#[ORM\Entity]
class Commission implements ResourceInterface
{
    public const CARREFOUR_COMMISSION = '10';
    public const PRINTEMPS_COMMISSION = '20';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'commission', type: 'integer', nullable: false)]
    private ?int $commission = null;

    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommission(): ?int
    {
        return $this->commission;
    }

    public function setCommission(int $commission): void
    {
        $this->commission = $commission;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }
}
