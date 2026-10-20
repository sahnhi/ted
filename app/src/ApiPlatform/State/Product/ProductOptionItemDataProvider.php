<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Product;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\Sylius\ProductOption;
use Sylius\Component\Product\Repository\ProductOptionRepositoryInterface;

class ProductOptionItemDataProvider implements RestrictedDataProviderInterface, ItemDataProviderInterface
{
    public function __construct(
        private readonly ProductOptionRepositoryInterface $productOptionRepository
    ) {
    }

    public function getItem(string $resourceClass, $id, ?string $operationName = null, array $context = []): ProductOption|null
    {
        /** @var ProductOption|null $productOption */
        $productOption = $this->productOptionRepository->findOneBy(['code' => $id]);

        if (null === $productOption) {
            return null;
        }

        $productOption->setValues($productOption->getEnabledValues());

        return $productOption;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return ProductOption::class === $resourceClass;
    }
}
