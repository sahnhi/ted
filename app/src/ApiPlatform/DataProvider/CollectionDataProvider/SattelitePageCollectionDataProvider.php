<?php

declare(strict_types=1);

namespace App\ApiPlatform\DataProvider\CollectionDataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Doctrine\Entity\SatellitePages\SatellitePage;
use App\Doctrine\Entity\SatellitePages\SatellitePageTranslation;
use App\Doctrine\Entity\Sylius\ProductOption;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Product\Resolver\AvailableProductOptionValuesResolverInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class SattelitePageCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly RepositoryInterface $satellitePageTranslationRepository,
        private readonly AvailableProductOptionValuesResolverInterface $availableProductOptionValuesResolver,
    ) {
    }

    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = []): iterable
    {
        /** @var SatellitePageTranslation|null $satellitePageTranslation */
        $satellitePageTranslation = $this->satellitePageTranslationRepository->findOneBy([
            'satelliteUrl' => $context['filters']['translations.satelliteUrl'],
        ]);

        if (null === $satellitePageTranslation) {
            return [];
        }

        /** @var SatellitePage $satellitePage */
        $satellitePage = $satellitePageTranslation->getTranslatable();

        $product = $satellitePage->getProduct();
        $product->setOptions($product->getEnabledOptions());

        /** @var ProductOption $option */
        foreach ($product->getOptions() as $option) {
            $availableValues = $this->availableProductOptionValuesResolver->resolve($product, $option);

            $availableValues
                ->filter(static fn (ProductOptionValue $value): bool => $value->getEnabled());

            // This is needed to prevent APIP to return the indexes of the values in the Collection
            $option->setValues(new ArrayCollection($availableValues->getValues()));
        }

        return [$satellitePage];
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return SatellitePage::class === $resourceClass && 'get' === $operationName;
    }
}
