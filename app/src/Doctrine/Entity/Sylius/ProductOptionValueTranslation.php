<?php

namespace App\Doctrine\Entity\Sylius;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOptionValueTranslation as BaseProductOptionValueTranslation;

#[ORM\Table(name: 'sylius_product_option_value_translation')]
#[ORM\Entity]
class ProductOptionValueTranslation extends BaseProductOptionValueTranslation implements ProductOptionValueTranslationInterface
{
    #[ORM\Column(name: 'help', type: 'text', nullable: true)]
    private ?string $help = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'optgroup', type: 'smallint', options: ['default' => '1'])]
    private int $optgroup = 1;

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(string $help): void
    {
        $this->help = $help;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getOptgroup(): ?int
    {
        return $this->optgroup;
    }

    public function setOptgroup(int $optgroup): void
    {
        $this->optgroup = $optgroup;
    }
}
