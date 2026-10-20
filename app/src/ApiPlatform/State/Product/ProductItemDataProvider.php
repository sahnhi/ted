<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Product;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductOption;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Product\Resolver\AvailableProductOptionValuesResolverInterface;

class ProductItemDataProvider implements ProductItemDataProviderInterface
{
    public function __construct(
        private readonly RestrictedDataProviderInterface&ItemDataProviderInterface $decoratedProductItemDataProvider,
        private readonly AvailableProductOptionValuesResolverInterface $availableProductOptionValuesResolver,
    ) {
    }

    public function getItem(string $resourceClass, $id, ?string $operationName = null, array $context = []): Product|null
    {
        /** @var Product|null $product */
        $product = $this->decoratedProductItemDataProvider->getItem($resourceClass, $id, $operationName, $context);

        if (null === $product) {
            return null;
        }

        $product->setOptions($product->getEnabledOptions());

        /** @var ProductOption $option */
        foreach ($product->getOptions() as $option) {
            $availableValues = $this->availableProductOptionValuesResolver->resolve($product, $option);

            $availableValues
                ->filter(static fn (ProductOptionValue $value): bool => $value->getEnabled());

            // This is needed to prevent APIP to return the indexes of the values in the Collection
            $option->setValues(new ArrayCollection($availableValues->getValues()));
        }

        return $product;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return $this->decoratedProductItemDataProvider->supports($resourceClass, $operationName, $context);
    }
}
