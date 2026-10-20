<?php

declare(strict_types=1);

namespace App\ApiPlatform\State;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;

class TaxonCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly CollectionDataProviderInterface&RestrictedDataProviderInterface $decoratedCollectionDataProvider
    ) {
    }

    public function getCollection(string $resourceClass, ?string $operationName = null): iterable
    {
        return $this->decoratedCollectionDataProvider->getCollection($resourceClass, $operationName);
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        // Sylius' TaxonCollectionDataProviders does not allow filtering, we therefore use ApiPlatform's default
        // CollectionDataProvider if filters are used
        return !array_key_exists('filters', $context)
            && $this->decoratedCollectionDataProvider->supports($resourceClass, $operationName, $context);
    }
}
