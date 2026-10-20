<?php

namespace App\Factory\View;

final class LimitedProductAttributeValuesViewFactory implements ProductAttributeValuesViewFactoryInterface
{
    public function __construct(
        private readonly ProductAttributeValueViewFactoryInterface $productAttributeValueViewFactory,
        private readonly array $allowedAttributesCodes
    ) {
    }

    public function create(array $attributeValues, string $locale): array
    {
        $attributeValuesView = [];
        foreach ($attributeValues as $attributeValue) {
            if (!in_array($attributeValue->getCode(), $this->allowedAttributesCodes, true)) {
                continue;
            }

            $attributeValuesView[] = $this->productAttributeValueViewFactory->create($attributeValue, $locale);
        }

        return $attributeValuesView;
    }
}
