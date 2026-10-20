<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOptionInterface;
use App\Doctrine\Entity\Sylius\ProductOptionValueInterface;
use App\Doctrine\Entity\Sylius\ShippingCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Sylius\Component\Shipping\Model\ShippingCategoryInterface;

#[ORM\Table(name: 'app_bundle_generator')]
#[ORM\Entity]
class BundleGenerator implements BundleGeneratorInterface
{
    use TimestampableTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    private ProductInterface $product;

    #[ORM\OneToMany(mappedBy: 'bundleGenerator', targetEntity: BundleGeneratorOption::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private ?Collection $options = null;

    #[ORM\OneToMany(mappedBy: 'bundleGenerator', targetEntity: BundleGeneratorCombination::class, cascade: ['all'], orphanRemoval: true)]
    private ?Collection $combinations = null;

    #[ORM\OneToMany(mappedBy: 'bundleGenerator', targetEntity: BundleGeneratorProduct::class, cascade: ['all'], orphanRemoval: true)]
    private ?Collection $products = null;

    #[ORM\Column(name: 'prefix', type: 'string', nullable: false)]
    private string $prefix = 'BUNDLE';

    #[ORM\Column(name: 'suffix', type: 'string', nullable: false)]
    private string $suffix = '';

    #[ORM\Column(name: 'nb_products', type: 'integer', nullable: false, options: ['default' => '4'])]
    private int $nbProducts = 4;

    #[ORM\Column(name: 'is_generated', type: 'boolean', nullable: false)]
    private bool $isGenerated = false;

    #[ORM\JoinColumn(name: 'shipping_category_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ShippingCategory::class)]
    private ?ShippingCategoryInterface $shippingCategory = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Timestampable(on: 'create')]
    protected $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    #[Timestampable(on: 'update')]
    protected $updatedAt;

    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->combinations = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    public function setProduct(ProductInterface $product): void
    {
        $this->product = $product;
    }

    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function getCombinations(): Collection
    {
        return $this->combinations;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    public function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }

    public function getNbProducts(): int
    {
        return $this->nbProducts;
    }

    public function setNbProducts(int $nbProducts): void
    {
        $this->nbProducts = $nbProducts;
    }

    public function addOption(BundleGeneratorOptionInterface $generatorOption): void
    {
        if (!$this->options->contains($generatorOption)) {
            $this->options->add($generatorOption);
        }
    }

    public function removeOption(BundleGeneratorOptionInterface $generatorOption): void
    {
        if ($this->options->contains($generatorOption)) {
            $this->options->removeElement($generatorOption);
        }
    }

    public function addCombination(BundleGeneratorCombinationInterface $combination): void
    {
        if (!$this->combinations->contains($combination)) {
            $this->combinations->add($combination);
        }
    }

    public function removeCombination(BundleGeneratorCombinationInterface $combination): void
    {
        if ($this->combinations->contains($combination)) {
            $this->combinations->removeElement($combination);
        }
    }

    public function addProduct(BundleGeneratorProductInterface $generatorProduct): void
    {
        if (!$this->products->contains($generatorProduct)) {
            $this->products->add($generatorProduct);
        }
    }

    public function removeProduct(BundleGeneratorProductInterface $generatorProduct): void
    {
        if ($this->products->contains($generatorProduct)) {
            $this->products->removeElement($generatorProduct);
        }
    }

    public function isGenerated(): bool
    {
        return $this->isGenerated;
    }

    public function setIsGenerated(bool $isGenerated): void
    {
        $this->isGenerated = $isGenerated;
    }

    public function getGeneratorOptionForOption(ProductOptionInterface $option): ?BundleGeneratorOptionInterface
    {
        $filtered = $this->options->filter(fn (BundleGeneratorOptionInterface $optionGenerator): bool => $optionGenerator->getProductOption() === $option);

        return $filtered->isEmpty() ? null : $filtered->first();
    }

    public function getGeneratorCombinationForOptionValues(array $optionValues): ?BundleGeneratorCombinationInterface
    {
        $filtered = $this->combinations->filter(function (BundleGeneratorCombinationInterface $combination) use ($optionValues): bool {
            /** @var ProductOptionValueInterface $optionValue */
            foreach ($optionValues as $optionValue) {
                if (!$combination->getOptionValues()->contains($optionValue)) {
                    return false;
                }
            }

            return true;
        });

        return $filtered->isEmpty() ? null : $filtered->first();
    }

    public function getCombinationsToGenerate(): Collection
    {
        return $this->combinations->filter(fn (BundleGeneratorCombinationInterface $combinationConfiguration): bool => $combinationConfiguration->isWillBeGenerated());
    }

    public function hasCombinationToGenerate(): bool
    {
        return !$this->getCombinationsToGenerate()->isEmpty();
    }

    public function getFormatedValueForCode(ProductOptionValueInterface $optionValue): string
    {
        $splited = explode('_', (string) $optionValue->getCode());
        $value = end($splited);

        return preg_replace('/[^A-Za-z0-9]/', '', strtoupper($value));
    }

    public function generateCodeForCombination(BundleGeneratorCombinationInterface $combinationConfiguration): string
    {
        $values = [];

        /** @var BundleGeneratorOptionInterface $option */
        foreach ($this->getOptions() as $option) {
            $optionValue = $combinationConfiguration->getValueForOption($option->getProductOption());
            $values[] = ($option->isNeedsUnderscore() ? '_' : '') . $this->getFormatedValueForCode($optionValue);
        }

        return $this->prefix . implode('', $values) . $this->suffix;
    }

    public function getShippingCategory(): ?ShippingCategoryInterface
    {
        return $this->shippingCategory;
    }

    public function setShippingCategory(?ShippingCategoryInterface $shippingCategory): void
    {
        $this->shippingCategory = $shippingCategory;
    }
}
