<?php

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\ProductReview;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Core\Repository\ProductReviewRepositoryInterface as BaseProductReviewRepositoryInterface;
use Sylius\Component\Review\Model\ReviewInterface;

interface ProductReviewRepositoryInterface extends BaseProductReviewRepositoryInterface
{
    /**
     * @return QueryBuilder|ReviewInterface[]
     */
    public function createListQueryBuilder(): QueryBuilder;

    /**
     * @return array
     */
    public function csvListQueryBuilder(array $criteria = []): IterableResult;

    /**
     * @return array|ReviewInterface[]
     */
    public function findUnrejectedByProductCodeAndChannel(string $code, string $locale, ChannelInterface $channel, ?array $filter, ?array $allQuestions): array;

    public function getAverageRatingsAndCountReviews(int $taxonId): array;

    /**
     * @return ProductReview[]
     */
    public function getReviewsByTaxon(Taxon $taxon, string $locale, ChannelInterface $channel, ?array $filter): array;

    public function getIterableRssReviews(int $index, int $limit): iterable;

    public function countRssReview(): int;

    public function countAllForProduct(ProductInterface $product);

    public function getReviewDistributionForProduct(ProductInterface $product);
}
