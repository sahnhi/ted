<?php

namespace App\Doctrine\Entity\Sylius;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOptionValue as BaseProductOptionValue;
use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface as BaseProductOptionValueTranslationInterface;

#[ORM\Table(name: 'sylius_product_option_value')]
#[ORM\Entity]
class ProductOptionValue extends BaseProductOptionValue implements ProductOptionValueInterface
{
    #[ORM\Column(name: 'enabled', type: 'boolean')]
    private ?bool $enabled = null;

    #[ORM\Column(name: 'position', type: 'integer', nullable: true)]
    private ?int $position = null;

    public function getTranslation(?string $locale = null): ProductOptionValueTranslationInterface
    {
        /** @var ProductOptionValueTranslationInterface $translation */
        $translation = parent::getTranslation($locale);

        return $translation;
    }

    public function getHelp(): ?string
    {
        return $this->getTranslation()->getHelp();
    }

    public function setHelp(string $help): void
    {
        $this->getTranslation()->setHelp($help);
    }

    public function getDescription(): ?string
    {
        return $this->getTranslation()->getDescription();
    }

    public function setDescription(string $description): void
    {
        $this->getTranslation()->setDescription($description);
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position = null): void
    {
        $this->position = $position;
    }

    protected function createTranslation(): BaseProductOptionValueTranslationInterface
    {
        return new ProductOptionValueTranslation();
    }
}
