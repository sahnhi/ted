<?php

namespace App\Factory\View;

use App\View\ProductAttributeValueView;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;

class ProductAttributeValueViewFactory implements ProductAttributeValueViewFactoryInterface
{
    public function create(ProductAttributeValueInterface $productAttributeValue, string $locale): ProductAttributeValueView
    {
        $productAttributeValueView = new ProductAttributeValueView();
        $productAttributeValueView->code = $productAttributeValue->getCode();
        $productAttributeValueView->value = $productAttributeValue->getValue();

        $productAttribute = $productAttributeValue->getAttribute();
        $productAttributeTranslation = $productAttribute->getTranslation($locale);
        $productAttributeValueView->name = $productAttributeTranslation->getName();

        return $productAttributeValueView;
    }
}
