<?php

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductOptionInterface;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Setono\SyliusCatalogPromotionPlugin\Doctrine\ORM\ProductVariantRepositoryTrait as CatalogPromotionProductVariantRepositoryTrait;
use Setono\SyliusCatalogPromotionPlugin\Repository\ProductVariantRepositoryInterface as CatalogPromotionProductVariantRepositoryInterface;
use Sylius\AdminOrderCreationPlugin\Doctrine\ORM\ProductVariantRepositoryInterface as AdminOrderCreationPluginProductVariantRepositoryInterface;
use Sylius\AdminOrderCreationPlugin\Doctrine\ORM\ProductVariantRepositoryTrait;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductVariantRepository as BaseProductVariantRepository;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use SyliusLabs\AssociationHydrator\AssociationHydrator;

class ProductVariantRepository extends BaseProductVariantRepository implements ProductVariantRepositoryInterface, AdminOrderCreationPluginProductVariantRepositoryInterface, CatalogPromotionProductVariantRepositoryInterface
{
    use ProductVariantRepositoryTrait;
    use CatalogPromotionProductVariantRepositoryTrait;

    protected AssociationHydrator $associationHydrator;

    public function __construct(EntityManagerInterface $em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);

        $this->associationHydrator = new AssociationHydrator($em, $class);
    }

    public function findByNamePart(string $phrase, string $locale, AdminUserInterface $user): array
    {
        $query = $this->createQueryBuilder('o')
            ->select('CONCAT(translation.name, \' (\', o.code, \')\') AS descriptor')
            ->addSelect('o.code')
            ->addSelect('o.id')
            ->innerJoin('o.translations', 'translation')
            ->andWhere('translation.name LIKE :phrase OR o.code LIKE :phrase')
            ->andWhere('translation.locale = :locale');

        if (!$user->hasRole(AdminUserInterface::DEFAULT_ADMIN_ROLE)) {
            $query->andWhere('o.enabled = 1');
        }

        return $query->setParameter('phrase', '%' . $phrase . '%')
            ->setParameter('locale', $locale)
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    public function findByOptions(string $productCode, array $options): array
    {
        $qb = $this
            ->createQueryBuilder('pv')
            ->innerJoin('pv.product', 'p')
            ->andWhere('p.code = :productCode')
            ->setParameter('productCode', $productCode);

        $i = 0;
        foreach ($options as $optionCode => $valueCode) {
            $ovAlias = sprintf('ov%d', $i);
            $oAlias = sprintf('o%d', $i);
            $optionParameter = sprintf('code%d', $i);
            $valueParameter = sprintf('value%d', $i);

            $qb
                ->innerJoin('pv.optionValues', $ovAlias)
                ->innerJoin(sprintf('%s.option', $ovAlias), $oAlias)
                ->andWhere(sprintf('%s.code = :%s', $oAlias, $optionParameter))
                ->andWhere(sprintf('%s.code = :%s', $ovAlias, $valueParameter))
                ->setParameter($optionParameter, $optionCode)
                ->setParameter($valueParameter, $valueCode);
            ++$i;
        }

        return $qb->getQuery()->getResult();
    }

    public function createQueryBuilderByChannelAndLocaleAndCode(ChannelInterface $channel, string $locale, string $code): QueryBuilder
    {
        return $this
            ->createQueryBuilder('pv')
            ->innerJoin('pv.product', 'p')
            ->addSelect('p')
            ->innerJoin('pv.translations', 'pt', 'WITH', 'pt.locale = :locale')
            ->andWhere('pv.code = :code')
            ->andWhere(':channel MEMBER OF p.channels')
            ->setParameter('code', $code)
            ->setParameter('channel', $channel)
            ->setParameter('locale', $locale);
    }

    public function createQueryBuilderByChannelAndCode(ChannelInterface $channel, string $code): QueryBuilder
    {
        return $this
            ->createQueryBuilder('pv')
            ->innerJoin('pv.product', 'p')
            ->addSelect('p')
            ->andWhere('pv.code = :code')
            ->andWhere(':channel MEMBER OF p.channels')
            ->setParameter('code', $code)
            ->setParameter('channel', $channel);
    }

    public function findByPhrase(string $phrase, string $locale): array
    {
        $expr = $this->getEntityManager()->getExpressionBuilder();

        return $this->createQueryBuilder('o')
            ->select('CONCAT(translation.name, \' (\', o.code, \')\') AS descriptor')
            ->addSelect('o.code')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->andWhere($expr->orX('translation.name LIKE :phrase', 'o.code LIKE :phrase'))
            ->setParameter('phrase', '%' . $phrase . '%')
            ->setParameter('locale', $locale)
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    public function getProductVariantNotIn(array $productCodes): array
    {
        $qb = $this->createQueryBuilder('o');

        return $qb
            ->andWhere($qb->expr()->notIn('o.code', ':data'))
            ->andWhere('o.tracked = :tracked')
            ->setParameter('data', implode(',', $productCodes))
            ->setParameter('tracked', true)
            ->getQuery()
            ->getResult();
    }

    public function findOneByCodeAndProductCode(string $code, string $productCode): ?ProductVariantInterface
    {
        $productVariant = $this->createQueryBuilder('o')
            ->innerJoin('o.product', 'product')
            ->andWhere('product.code = :productCode')
            ->andWhere('o.code = :code')
            ->setParameter('productCode', $productCode)
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();

        $this->associationHydrator->hydrateAssociations($productVariant, [
            'channelPricings',
            'optionValues',
        ]);

        return $productVariant;
    }

    public function findOneByEnabledProductVariantCodeAndProductCode(string $code, string $productCode): ?ProductVariantInterface
    {
        $productVariant = $this->createQueryBuilder('o')
            ->innerJoin('o.product', 'product')
            ->andWhere('product.code = :productCode')
            ->andWhere('o.code = :code')
            ->andWhere('o.enabled = :enabled')
            ->setParameter('productCode', $productCode)
            ->setParameter('code', $code)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getOneOrNullResult();

        $this->associationHydrator->hydrateAssociations($productVariant, [
            'channelPricings',
            'optionValues',
        ]);

        return $productVariant;
    }

    public function findOneByCode(string $code): ?ProductVariantInterface
    {
        $productVariant = $this->createQueryBuilder('o')
            ->andWhere('o.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();

        $this->associationHydrator->hydrateAssociations($productVariant, [
            'channelPricings',
            'optionValues',
        ]);

        return $productVariant;
    }

    public function findEnabledByCode(array $codes): array
    {
        return $this->createQueryBuilder('pv')
            ->innerJoin('pv.product', 'p')
            ->andWhere('pv.code IN (:codes)')
            ->andWhere('pv.enabled = true')
            ->andWhere('p.enabled = true')
            ->setParameter('codes', $codes)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getFirstAvailableShippingDate(string $code, \DateTimeInterface $date): \DateTimeInterface
    {
        $productVariant = $this->createQueryBuilder('o')
            ->addSelect('stock.shippingDate')
            ->innerJoin('o.shippingStock', 'stock')
            ->andWhere('o.code = :code')
            ->orderBy('stock.shippingDate', 'ASC')
            ->setMaxResults(1)
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();

        return null !== $productVariant && $productVariant['shippingDate'] > $date ?
            $productVariant['shippingDate'] :
            $date;
    }

    public function createInventoryListQueryBuilder(string $locale): QueryBuilder
    {
        $queryBuilder = parent::createInventoryListQueryBuilder($locale);
        $queryBuilder->leftJoin('o.shippingStock', 'shippingStock');

        return $queryBuilder;
    }

    public function findComboCodesByCode($code): array
    {
        return $this->createQueryBuilder('pv')
            ->where("pv.code LIKE :underscore ESCAPE '/'")
            ->andWhere('pv.code LIKE :code')
            ->setParameter('underscore', '%/_%')
            ->setParameter('code', '%' . $code . '%')
            ->getQuery()->getResult();
    }

    public function findByNamePartAndChannel(string $phrase, string $locale, AdminUserInterface $user, ?string $channelCode = null): array
    {
        $query = $this->createQueryBuilder('o')
            ->select('CONCAT(translation.name, \' (\', o.code, \')\') AS descriptor')
            ->addSelect('o.code')
            ->innerJoin('o.translations', 'translation')
            ->leftJoin('o.product', 'p')
            ->andWhere('translation.name LIKE :phrase OR o.code LIKE :phrase')
            ->andWhere('translation.locale = :locale');

        if (!$user->hasRole(AdminUserInterface::DEFAULT_ADMIN_ROLE)) {
            $query->andWhere('o.enabled = 1');
        }

        if (null !== $channelCode) {
            $query->leftJoin('p.channels', 'c')
                ->andWhere('c.code = :channelCode')
                ->setParameter('channelCode', $channelCode);
        }

        return $query->setParameter('phrase', '%' . $phrase . '%')
            ->setParameter('locale', $locale)
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    public function findOneBySearch(string $criteria, string $locale): ?ProductVariantInterface
    {
        return $this->createQueryBuilder('v')
            ->innerJoin('v.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->where('v.code LIKE :criteria')
            ->orWhere('translation.name LIKE :criteria')
            ->setParameter('locale', $locale)
            ->setParameter('criteria', '%' . $criteria . '%')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    public function findOutOfStockVariants()
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.shippingStock', 's')
            ->leftJoin('s.warehouse', 'w')
            ->where('(w.id = 1 OR w.id IS NULL)')
            ->andWhere('(s.shippingDate is not null OR v.enabled = 0)')
            ->getQuery()->getResult();
    }

    public function findByChannelWithPrices($channel, $limit, $offset)
    {
        $results = $this->createQueryBuilder('v')
            ->leftJoin('v.product', 'p')
            ->where(':channel MEMBER OF p.channels')
            ->setParameter('channel', $channel)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()->getResult();

        $this->associationHydrator->hydrateAssociations($results, [
            'channelPricings',
        ]);

        return $results;
    }

    public function findByNamePartNoReturn(string $phrase, string $locale, AdminUserInterface $user): array
    {
        $query = $this->createQueryBuilder('o')
            ->select('CONCAT(translation.name, \' (\', o.code, \')\') AS descriptor')
            ->addSelect('o.code')
            ->innerJoin('o.translations', 'translation')
            ->andWhere('translation.name LIKE :phrase OR o.code LIKE :phrase')
            ->andWhere('translation.locale = :locale')
            ->andWhere("o.code NOT LIKE 'R%'");

        if (!$user->hasRole(AdminUserInterface::DEFAULT_ADMIN_ROLE)) {
            $query->andWhere('o.enabled = 1');
        }

        return $query->setParameter('phrase', '%' . $phrase . '%')
            ->setParameter('locale', $locale)
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    private function createCheapestQueryBuilder(ChannelInterface $channel): QueryBuilder
    {
        return $this->createQueryBuilder('v')
            ->join('v.channelPricings', 'cp')
            ->addSelect('cp')
            ->where('cp.channelCode = :channel')
            ->setParameter('channel', $channel->getCode())
            ->orderBy('cp.price', 'ASC')
            ->setMaxResults(1);
    }

    public function getCheapestByOptionValue(ProductInterface $product, string $optionValueCode, ChannelInterface $channel): ?ProductVariantInterface
    {
        return $this->createCheapestQueryBuilder($channel)
            ->join('v.optionValues', 'o')
            ->andWhere('o.code = :code')
            ->andWhere('v.product = :product')
            ->setParameter('code', $optionValueCode)
            ->setParameter('product', $product)
            ->getQuery()->getOneOrNullResult();
    }

    public function getCheapestByOption(ProductInterface $product, ProductOptionInterface $productOption, ChannelInterface $channel): ?ProductVariantInterface
    {
        return $this->createCheapestQueryBuilder($channel)
            ->join('v.optionValues', 'ov')
            ->andWhere('ov.option = :option')
            ->andWhere('v.product = :product')
            ->setParameter('option', $productOption)
            ->setParameter('product', $product)
            ->getQuery()->getOneOrNullResult();
    }

    public function getCheapestByProduct(ProductInterface $product, ChannelInterface $channel): ?ProductVariantInterface
    {
        return $this->createCheapestQueryBuilder($channel)
            ->andWhere('v.enabled = 1')
            ->andWhere('v.product = :product')
            ->setParameter('product', $product)
            ->getQuery()->getOneOrNullResult();
    }

    public function comparePrices(ChannelPricingInterface $itemA, ChannelPricingInterface $itemB): int
    {
        return $itemA->getPrice() <=> $itemB->getPrice();
    }

    public function findCheapestPrice(ProductInterface $product, ChannelInterface $channel): ?ProductVariantInterface
    {
        $minProductVariant = null;
        $productVariants = $product->getVariants();

        /**
         * @var ProductVariantInterface $productVariant
         */
        foreach ($productVariants as $productVariant) {
            if ($productVariant->getChannelPricings()->isEmpty()
                || is_null($productVariant->getChannelPricings()->get($channel->getCode()))
            ) {
                continue;
            }

            if (null === $minProductVariant
                || $this->comparePrices(
                    $productVariant->getChannelPricings()->get($channel->getCode()),
                    $minProductVariant->getChannelPricings()->get($channel->getCode())
                ) < 0) {
                if ($productVariant->isEnabled()) {
                    $minProductVariant = $productVariant;
                }
            }
        }

        unset($productVariants);

        return $minProductVariant;
    }

    public function getForFilter(string $localeCode)
    {
        $items = $this->createQueryBuilder('pv')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();

        $this->associationHydrator->hydrateAssociations($items, [
            'optionValues',
            'optionValues.translations',
            'product',
            'translations',
        ]);

        /** @var ProductVariantInterface $item */
        foreach ($items as $item) {
            $item->setCurrentLocale($localeCode);
        }

        return $items;
    }

    public function getBundlesQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('pv')
            ->distinct()
            ->innerJoin('pv.bundleAssociations', 'ba')
            ->orderBy('pv.createdAt', 'DESC');
    }

    public function findByProductCode(string $code): array
    {
        return $this->createQueryBuilder('pv')
            ->innerJoin('pv.product', 'p')
            ->andWhere('p.code = :code')
            ->andWhere('pv.enabled = 1')
            ->setParameter('code', $code)
            ->getQuery()->getResult();
    }

    public function findAllByDimensionsAndProductCode(string $code, ?array $widths = null, ?array $lengths = null): mixed
    {
        $builder = $this->createQueryBuilder('v')
            ->select(['v.width', 'v.depth AS length'])
            ->distinct()
            ->innerJoin('v.product', 'p')
            ->andWhere('p.code = :code')
            ->andWhere('v.enabled = 1')
            ->andWhere('v.width IS NOT NULL')
            ->andWhere('v.depth IS NOT NULL')
            ->setParameter('code', $code);

        if (null !== $widths) {
            $builder->andWhere('v.width IN (:widths)')
                ->setParameter('widths', $widths);
        }
        if (null !== $lengths) {
            $builder->andWhere('v.depth IN (:lengths)')
                ->setParameter('lengths', $lengths);
        }

        return $builder->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneEnabledByCode(string $code): ?ProductVariantInterface
    {
        return $this->createQueryBuilder('pv')
            ->innerJoin('pv.product', 'p')
            ->andWhere('pv.enabled = true')
            ->andWhere('p.enabled = true')
            ->andWhere('pv.code = :code')
            ->setParameter('code', $code, Types::STRING)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
