<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Order;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Factory\Logistic\PickUp\SearchableAddressFactory;
use App\Model\Logistic\PickUp\PickUpPoints;
use App\Provider\Logistic\PickUpPointProvider;

final class PickUpPointCollectionProvider implements RestrictedDataProviderInterface, ItemDataProviderInterface
{
    public function __construct(
        private readonly SearchableAddressFactory $searchableAddressFactory,
        private readonly PickUpPointProvider $pickUpPointProvider
    ) {
    }

    public function getItem(string $resourceClass, $id, ?string $operationName = null, array $context = []): PickUpPoints
    {
        $query = $this->searchableAddressFactory->createFromProvider($context['filters'], $id);

        return $this->pickUpPointProvider->fetch($query, 'dpd');
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return 'pick-up' === $operationName;
    }
}
