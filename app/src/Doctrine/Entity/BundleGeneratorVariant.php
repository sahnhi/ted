<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'app_bundle_generator_variant')]
#[ORM\Entity]
class BundleGeneratorVariant implements BundleGeneratorVariantInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'bundle_generator_product_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BundleGeneratorProduct::class)]
    private BundleGeneratorProductInterface $product;

    #[ORM\JoinColumn(name: 'variant_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
    private ?ProductVariantInterface $variant = null;

    #[ORM\JoinColumn(name: 'bundle_generator_combination_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BundleGeneratorCombination::class, inversedBy: 'variants')]
    private BundleGeneratorCombinationInterface $combination;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): BundleGeneratorProductInterface
    {
        return $this->product;
    }

    public function setProduct(BundleGeneratorProductInterface $product): void
    {
        $this->product = $product;
    }

    public function getCombination(): BundleGeneratorCombinationInterface
    {
        return $this->combination;
    }

    public function setCombination(BundleGeneratorCombinationInterface $combination): void
    {
        $this->combination = $combination;
    }

    public function getVariant(): ?ProductVariantInterface
    {
        return $this->variant;
    }

    public function setVariant(?ProductVariantInterface $variant): void
    {
        $this->variant = $variant;
    }
}
