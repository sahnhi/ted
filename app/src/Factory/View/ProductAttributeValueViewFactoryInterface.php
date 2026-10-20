<?php

namespace App\Factory\View;

use App\View\ProductAttributeValueView;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;

interface ProductAttributeValueViewFactoryInterface
{
    public function create(ProductAttributeValueInterface $productAttributeValue, string $locale): ProductAttributeValueView;
}
