<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductOption;
use App\Doctrine\Entity\Sylius\ProductOptionInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use App\Doctrine\Entity\Sylius\ProductOptionValueInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'app_bundle_generator_option')]
#[ORM\Entity]
class BundleGeneratorOption implements BundleGeneratorOptionInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'product_option_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: ProductOption::class)]
    private ProductOptionInterface $productOption;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    private ?int $position = 0;

    #[ORM\Column(name: 'forced_value', type: 'boolean', nullable: false)]
    private bool $forcedValue = false;

    #[ORM\Column(name: 'needs_underscore', type: 'boolean', nullable: false, options: ['default' => '1'])]
    private bool $needsUnderscore = true;

    #[ORM\JoinColumn(name: 'product_option_value_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: ProductOptionValue::class)]
    private ?ProductOptionValueInterface $productOptionValue = null;

    #[ORM\JoinColumn(name: 'bundle_generator_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BundleGenerator::class, fetch: 'EAGER', inversedBy: 'options')]
    private BundleGeneratorInterface $bundleGenerator;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductOption(): ProductOptionInterface
    {
        return $this->productOption;
    }

    public function setProductOption(ProductOptionInterface $productOption): void
    {
        $this->productOption = $productOption;
    }

    public function getProductOptionValue(): ?ProductOptionValueInterface
    {
        return $this->productOptionValue;
    }

    public function setProductOptionValue(?ProductOptionValueInterface $productOptionValue): void
    {
        $this->productOptionValue = $productOptionValue;
    }

    public function getBundleGenerator(): BundleGeneratorInterface
    {
        return $this->bundleGenerator;
    }

    public function setBundleGenerator(BundleGeneratorInterface $bundleGenerator): void
    {
        $this->bundleGenerator = $bundleGenerator;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function isForcedValue(): bool
    {
        return $this->forcedValue;
    }

    public function setForcedValue(bool $forcedValue): void
    {
        $this->forcedValue = $forcedValue;
    }

    public function isNeedsUnderscore(): bool
    {
        return $this->needsUnderscore;
    }

    public function setNeedsUnderscore(bool $needsUnderscore): void
    {
        $this->needsUnderscore = $needsUnderscore;
    }
}
