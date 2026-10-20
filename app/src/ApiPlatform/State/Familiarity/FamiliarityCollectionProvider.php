<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Familiarity;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\Familiarity;
use App\Doctrine\Repository\FamiliarityRepositoryInterface;

final class FamiliarityCollectionProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly FamiliarityRepositoryInterface $familiarityRepository
    ) {
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return Familiarity::class === $resourceClass;
    }

    /**
     * @return iterable<Familiarity>
     */
    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = []): iterable
    {
        return $this->familiarityRepository->findBy(['enabled' => true]);
    }
}
