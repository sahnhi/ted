<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'app_bundle_generator_product')]
#[ORM\Entity]
class BundleGeneratorProduct implements BundleGeneratorProductInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    private ?ProductInterface $product = null;

    #[ORM\Column(name: 'quantity', type: 'integer', nullable: false, options: ['default' => '1'])]
    private int $quantity = 1;

    #[ORM\Column(name: 'is_free', type: 'boolean', nullable: false)]
    private bool $isFree = false;

    #[ORM\JoinColumn(name: 'bundle_generator_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BundleGenerator::class, inversedBy: 'products')]
    private ?BundleGeneratorInterface $bundleGenerator = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): void
    {
        $this->product = $product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function isFree(): bool
    {
        return $this->isFree;
    }

    public function setIsFree(bool $isFree): void
    {
        $this->isFree = $isFree;
    }

    public function getBundleGenerator(): ?BundleGeneratorInterface
    {
        return $this->bundleGenerator;
    }

    public function setBundleGenerator(?BundleGeneratorInterface $bundleGenerator): void
    {
        $this->bundleGenerator = $bundleGenerator;
    }
}
