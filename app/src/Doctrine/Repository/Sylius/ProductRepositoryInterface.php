<?php

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\Product;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface as BaseProductRepositoryInterface;

interface ProductRepositoryInterface extends BaseProductRepositoryInterface
{
    public function findAllByChannelAndLocale(ChannelInterface $channel, string $locale): array;

    public function findNumberByCodeOrName(string $criteria, string $locale): int;

    public function findAllForBundlesQueryBuilder(): array;

    /**
     * @return array<Product>
     */
    public function findAllWithReviewRequested(string $locale, array $productCodes, array $questionsAnswered): array;

    public function findBySearch(string $term, string $locale): mixed;
}
