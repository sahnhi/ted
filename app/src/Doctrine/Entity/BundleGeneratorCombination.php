<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\ProductOptionInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use App\Doctrine\Entity\Sylius\ProductOptionValueInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'app_bundle_generator_combination')]
#[ORM\Entity]
class BundleGeneratorCombination implements BundleGeneratorCombinationInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'already_exists', type: 'boolean', nullable: false)]
    private bool $alreadyExists = false;

    #[ORM\Column(name: 'will_be_generated', type: 'boolean', nullable: false, options: ['default' => '1'])]
    private bool $willBeGenerated = true;

    #[ORM\Column(name: 'code', type: 'string', nullable: true)]
    private ?string $code = null;

    #[ORM\OneToMany(mappedBy: 'combination', targetEntity: BundleGeneratorVariant::class, cascade: ['all'], orphanRemoval: true)]
    private ?Collection $variants;

    #[ORM\JoinColumn(name: 'bundle_generator_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BundleGenerator::class, fetch: 'EAGER', inversedBy: 'combinations')]
    private ?BundleGeneratorInterface $bundleGenerator = null;

    #[ORM\JoinTable(name: 'app_bundle_generator_combination_product_option_value')]
    #[ORM\JoinColumn(name: 'bundle_generator_combination_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'product_option_value_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: ProductOptionValue::class)]
    private ?Collection $optionValues;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->optionValues = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    public function isAlreadyExists(): bool
    {
        return $this->alreadyExists;
    }

    public function setAlreadyExists(bool $alreadyExists): void
    {
        $this->alreadyExists = $alreadyExists;
    }

    public function isWillBeGenerated(): bool
    {
        return $this->willBeGenerated;
    }

    public function setWillBeGenerated(bool $willBeGenerated): void
    {
        $this->willBeGenerated = $willBeGenerated;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getBundleGenerator(): ?BundleGeneratorInterface
    {
        return $this->bundleGenerator;
    }

    public function setBundleGenerator(?BundleGeneratorInterface $bundleGenerator): void
    {
        $this->bundleGenerator = $bundleGenerator;
    }

    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addOptionValue(ProductOptionValueInterface $productOptionValue): void
    {
        if (!$this->optionValues->contains($productOptionValue)) {
            $this->optionValues->add($productOptionValue);
        }
    }

    public function removeOptionValue(ProductOptionValueInterface $productOptionValue): void
    {
        if ($this->optionValues->contains($productOptionValue)) {
            $this->optionValues->removeElement($productOptionValue);
        }
    }

    public function addVariant(BundleGeneratorVariantInterface $variant): void
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
        }
    }

    public function removeVariant(BundleGeneratorVariantInterface $variant): void
    {
        if ($this->variants->contains($variant)) {
            $this->variants->removeElement($variant);
        }
    }

    public function getValueForOption(ProductOptionInterface $option): ?ProductOptionValueInterface
    {
        $filtered = $this->optionValues->filter(fn (ProductOptionValueInterface $productOptionValue): bool => $productOptionValue->getOption() === $option);

        return $filtered->isEmpty() ? null : $filtered->first();
    }

    public function getVariantFromProduct(BundleGeneratorProductInterface $product): ?BundleGeneratorVariantInterface
    {
        $filtered = $this->variants->filter(fn (?BundleGeneratorVariantInterface $variant): bool => $variant instanceof BundleGeneratorVariantInterface && $variant->getProduct() === $product);

        return $filtered->isEmpty() ? null : $filtered->first();
    }
}
