<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Product;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;

class ProductVariantItemDataProvider implements RestrictedDataProviderInterface, ItemDataProviderInterface
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository
    ) {
    }

    public function getItem(string $resourceClass, $id, ?string $operationName = null, array $context = []): ProductVariant|null
    {
        /** @var ProductVariant|null $productVariant */
        $productVariant = $this->productVariantRepository->findOneEnabledByCode($id);
        if (null === $productVariant) {
            return null;
        }

        $productVariant->setOptionValues($productVariant->getEnabledOptionValues());

        return $productVariant;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return ProductVariant::class === $resourceClass;
    }
}
