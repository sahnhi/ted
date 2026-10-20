<?php

declare(strict_types=1);

namespace App\ApiPlatform\State\Product;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\ApiPlatform\Model\ProductReviewStatsSummary;
use App\Doctrine\Repository\Sylius\ProductRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductReviewRepositoryInterface;

class ProductReviewStatCollectionProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductReviewRepositoryInterface $productReviewRepository
    ) {
    }

    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = []): array
    {
        $collection = [];
        foreach ((array) $context['filters']['reviewSubject'] as $productCode) {
            $product = $this->productRepository->findOneByCode($productCode);
            if (!$product) {
                continue;
            }

            $totalReview = $this->productReviewRepository->countAllForProduct($product);
            $distributionReview = $this->productReviewRepository->getReviewDistributionForProduct($product);
            $distribution['ratingDistribution'] = array_fill(1, 5, 0); // Initialize with zeros for ratings 1-5
            foreach ($distributionReview as $row) {
                $distribution['ratingDistribution'][$row['rating']] = (int) $row['count'];
            }
            $collection[] = new ProductReviewStatsSummary($productCode, $totalReview['numberOfRating'], round((float) $totalReview['averageRating'], 1), $distribution['ratingDistribution']);
        }

        return $collection;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return 'shop_get_stats' === $operationName;
    }
}
