<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\ProductOptionValueLocaleDefault;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductOptionRepository;
use Sylius\Component\Product\Model\ProductOption as BaseProductOption;

#[ORM\Table(name: 'sylius_product_option')]
#[ORM\Entity(repositoryClass: ProductOptionRepository::class)]
class ProductOption extends BaseProductOption implements ProductOptionInterface
{
    #[ORM\OneToMany(targetEntity: ProductOptionValueLocaleDefault::class, mappedBy: 'option')]
    private ?Collection $defaultOptionsValue = null;

    #[ORM\OneToMany(targetEntity: ProductOptionValue::class, mappedBy: 'option', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected $values;

    public function addDefaultOptionsValue(ProductOptionValueLocaleDefault $defaultOptionsValue): ProductOption
    {
        $this->defaultOptionsValue[] = $defaultOptionsValue;

        return $this;
    }

    public function removeDefaultOptionsValue(ProductOptionValueLocaleDefault $defaultOptionsValue): bool
    {
        return $this->defaultOptionsValue->removeElement($defaultOptionsValue);
    }

    public function getDefaultOptionValueByLocale(string $locale)
    {
        if (null !== $this->getDefaultOptionsValue()) {
            foreach ($this->getDefaultOptionsValue() as $defaultOptionValue) {
                if ($locale === $defaultOptionValue->getLocale()->getCode()) {
                    return $defaultOptionValue->getOptionValue()->getCode();
                }
            }
        }

        return '';
    }

    public function getDefaultOptionValueByLocaleAndProduct(string $locale, ProductInterface $product)
    {
        if (null !== $this->getDefaultOptionsValue()) {
            foreach ($this->getDefaultOptionsValue() as $defaultOptionValue) {
                if ($locale === $defaultOptionValue->getLocale()->getCode()
                    && $product === $defaultOptionValue->getProduct()) {
                    return $defaultOptionValue->getOptionValue()->getCode();
                }
            }
        }

        return $this->getDefaultOptionValueByLocale($locale);
    }

    public function getDefaultOptionsValue(): Collection
    {
        return $this->defaultOptionsValue;
    }

    public function getEnabledValues(): Collection
    {
        $enabledValues = new ArrayCollection();
        /** @var ProductOptionValue $value */
        foreach ($this->getValues() as $value) {
            if (!$value->getEnabled()) {
                continue;
            }

            $enabledValues->add($value);
        }

        return $enabledValues;
    }

    public function setValues(Collection $values): self
    {
        $this->values = $values;

        return $this;
    }

    public function getSiteName(): ?string
    {
        /** @var ProductOptionTranslation $trans */
        $trans = $this->getTranslation();

        return $trans->getSiteName();
    }

    public function setSiteName(?string $siteName): self
    {
        /** @var ProductOptionTranslation $trans */
        $trans = $this->getTranslation();
        $trans->setSiteName($siteName);

        return $this;
    }
}
