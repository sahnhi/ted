<?php

namespace App\Doctrine\Entity\Sylius;

use Sylius\Component\Product\Model\ProductOptionInterface as BaseProductOptionInterface;

interface ProductOptionInterface extends BaseProductOptionInterface
{
    public function getDefaultOptionValueByLocale(string $locale);
}
