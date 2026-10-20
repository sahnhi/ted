<?php

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOptionInterface;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Product\Repository\ProductVariantRepositoryInterface as BaseProductVariantRepositoryInterface;

interface ProductVariantRepositoryInterface extends BaseProductVariantRepositoryInterface
{
    public function findByNamePart(string $phrase, string $locale, AdminUserInterface $user): array;

    public function findByOptions(string $productCode, array $options): array;

    public function createQueryBuilderByChannelAndLocaleAndCode(ChannelInterface $channel, string $locale, string $code): QueryBuilder;

    public function createQueryBuilderByChannelAndCode(ChannelInterface $channel, string $code): QueryBuilder;

    public function findByPhrase(string $phrase, string $locale): array;

    public function getProductVariantNotIn(array $productCodes): array;

    public function findOneByCodeAndProductCode(string $code, string $productCode): ?ProductVariantInterface;

    public function findOneByEnabledProductVariantCodeAndProductCode(string $code, string $productCode): ?ProductVariantInterface;

    public function findOneByCode(string $code): ?ProductVariantInterface;

    public function getFirstAvailableShippingDate(string $code, \DateTimeInterface $date): \DateTimeInterface;

    public function findByNamePartAndChannel(string $phrase, string $locale, AdminUserInterface $user, ?string $channelCode = null): array;

    public function findOneBySearch(string $criteria, string $locale): ?ProductVariantInterface;

    public function getCheapestByOptionValue(ProductInterface $product, string $optionValueCode, ChannelInterface $channel): ?ProductVariantInterface;

    public function getCheapestByOption(ProductInterface $product, ProductOptionInterface $productOption, ChannelInterface $channel): ?ProductVariantInterface;

    public function getCheapestByProduct(ProductInterface $product, ChannelInterface $channel): ?ProductVariantInterface;

    public function findCheapestPrice(ProductInterface $product, ChannelInterface $channel): ?ProductVariantInterface;

    public function getBundlesQueryBuilder(): QueryBuilder;

    public function findByProductCode(string $code): array;

    public function findAllByDimensionsAndProductCode(string $code, ?array $widths = null, ?array $lengths = null);

    /**
     * @return array<ProductVariantInterface>
     */
    public function findEnabledByCode(array $codes): array;

    public function findOneEnabledByCode(string $code): ?ProductVariantInterface;
}
