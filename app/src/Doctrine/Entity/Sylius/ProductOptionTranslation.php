<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Sylius;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOptionTranslation as BaseProductOptionTranslation;
use Sylius\Component\Product\Model\ProductOptionTranslationInterface;

#[ORM\Table(name: 'sylius_product_option_translation')]
#[ORM\Entity]
class ProductOptionTranslation extends BaseProductOptionTranslation implements ProductOptionTranslationInterface
{
    #[ORM\Column(name: 'site_name', type: 'string', nullable: true)]
    private ?string $siteName = null;

    public function getSiteName(): ?string
    {
        return $this->siteName;
    }

    public function setSiteName(string $siteName): void
    {
        $this->siteName = $siteName;
    }
}
