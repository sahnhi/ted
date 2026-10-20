<?php

namespace App\ApiPlatform\DataProvider\ItemDataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\SatellitePages\SatellitePage;
use App\Doctrine\Entity\SatellitePages\SatellitePageTranslation;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class SatellitePagesItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly RepositoryInterface $satellitePageTranslationRepository
    ) {
    }

    public function getItem(string $resourceClass, $id, ?string $operationName = null, array $context = []): SatellitePage
    {
        $satellitePageTranslation = $this->satellitePageTranslationRepository->findOneBy(['satelliteUrl' => $id]);

        return $satellitePageTranslation?->getTranslatable();
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return SatellitePageTranslation::class === $resourceClass && 'get_by_satellite_url' === $operationName;
    }
}
