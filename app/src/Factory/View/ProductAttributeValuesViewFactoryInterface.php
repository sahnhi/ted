<?php

namespace App\Factory\View;

use App\View\ProductAttributeValueView;
use Sylius\Component\Attribute\Model\AttributeValueInterface;

interface ProductAttributeValuesViewFactoryInterface
{
    /**
     * @param array|AttributeValueInterface[] $attributeValues
     *
     * @return array|ProductAttributeValueView[]
     */
    public function create(array $attributeValues, string $locale): array;
}
