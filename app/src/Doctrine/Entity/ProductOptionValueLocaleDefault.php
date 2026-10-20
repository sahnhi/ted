<?php

namespace App\Doctrine\Entity;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductOption;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Locale\Model\Locale;

#[ORM\Table(name: 'sylius_product_option_value_locale_default')]
#[ORM\Entity]
class ProductOptionValueLocaleDefault
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Product::class)]
    private ?Product $product = null;

    #[ORM\JoinColumn(name: 'product_option_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductOption::class, inversedBy: 'defaultOptionsValue')]
    private ?ProductOption $option = null;

    #[ORM\JoinColumn(name: 'locale_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Locale::class)]
    private ?Locale $locale = null;

    #[ORM\JoinColumn(name: 'product_option_value_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ProductOptionValue::class)]
    private ?ProductOptionValue $optionValue = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product = null): ProductOptionValueLocaleDefault
    {
        $this->product = $product;

        return $this;
    }

    public function getOption(): ProductOption
    {
        return $this->option;
    }

    public function setOption(?ProductOption $option = null): ProductOptionValueLocaleDefault
    {
        $this->option = $option;

        return $this;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function setLocale(?Locale $locale = null): ProductOptionValueLocaleDefault
    {
        $this->locale = $locale;

        return $this;
    }

    public function getOptionValue(): ProductOptionValue
    {
        return $this->optionValue;
    }

    public function setOptionValue(?ProductOptionValue $optionValue = null): ProductOptionValueLocaleDefault
    {
        $this->optionValue = $optionValue;

        return $this;
    }
}
