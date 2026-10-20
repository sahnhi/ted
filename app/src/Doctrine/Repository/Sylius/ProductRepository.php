<?php

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\ProductInterface;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Setono\SyliusCatalogPromotionPlugin\Doctrine\ORM\ProductRepositoryTrait as CatalogPromotionProductRepositoryTrait;
use Setono\SyliusCatalogPromotionPlugin\Repository\ProductRepositoryInterface as CatalogPromotionProductRepositoryInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;
use Sylius\Component\Core\Model\ChannelInterface;
use SyliusLabs\AssociationHydrator\AssociationHydrator;

class ProductRepository extends BaseProductRepository implements ProductRepositoryInterface, CatalogPromotionProductRepositoryInterface
{
    use CatalogPromotionProductRepositoryTrait;

    private readonly AssociationHydrator $associationHydrator;

    public function __construct(EntityManager $entityManager, ClassMetadata $class)
    {
        parent::__construct($entityManager, $class);

        $this->associationHydrator = new AssociationHydrator($entityManager, $class);
    }

    public function findAllByChannelAndLocale(ChannelInterface $channel, string $locale): array
    {
        $products = $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->andWhere(':channel MEMBER OF o.channels')
            ->andWhere('o.enabled = true')
            ->addOrderBy('o.createdAt', 'DESC')
            ->setParameter('channel', $channel)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();

        $this->associationHydrator
            ->hydrateAssociations($products, [
                'attributes',
                'options',
                'options.defaultOptionsValue',
                'options.translations',
                'options.values',
                'productTaxons',
                'reviews',
                'variants',
                'variants.channelPricings',
                'variants.optionValues',
                'variants.optionValues.translations',
                'variants.packs',
                'variants.translations',
            ]);

        return $products;
    }

    public function listProductsWithQuestions(string $locale): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->addSelect('translation')
            ->leftJoin('p.translations', 'translation')
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->addOrderBy('translation.name', 'ASC');
    }

    public function listProductsWithOptions(string $locale): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->addSelect('translation')
            ->leftJoin('p.translations', 'translation')
            ->leftJoin('p.options', 'option')
            ->andWhere('translation.locale = :locale')
            ->andWhere('option IS NOT NULL')
            ->setParameter('locale', $locale)
            ->addOrderBy('translation.name', 'ASC');
    }

    public function findOrderedByChannelAndTaxonCode(ChannelInterface $channel, string $locale, string $code): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->innerJoin('o.channels', 'channel')
            ->andWhere('o.enabled = true')
            ->andWhere('channel = :channel')
            ->innerJoin('o.productTaxons', 'productTaxons')
            ->addOrderBy('productTaxons.position', 'asc')
            ->innerJoin('productTaxons.taxon', 'taxon')
            ->andWhere('taxon.code = :code')
            ->setParameter('code', $code)
            ->setParameter('channel', $channel)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ProductInterface
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNumberByCodeOrName(string $criteria, string $locale): int
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->innerJoin('p.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->where('p.code LIKE :criteria')
            ->orWhere('translation.slug LIKE :criteria')
            ->orWhere('translation.name LIKE :criteria')
            ->setParameter('locale', $locale)
            ->setParameter('criteria', '%' . $criteria . '%')
            ->getQuery()->getSingleScalarResult();
    }

    public function findAllByCode(array $codes)
    {
        return $this->createQueryBuilder('o')
           ->andWhere('o.code IN (:codes)')
           ->setParameter('codes', $codes)
           ->getQuery()
           ->getResult();
    }

    public function findAllWithReviewRequested(string $locale, array $productCodes, array $questionsAnswered): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftjoin('p.productReviewQuestions', 'pr')
            ->leftJoin('p.translations', 'pt', 'WITH', 'pt.locale = :locale')
            ->leftJoin('pr.translations', 'ot', 'WITH', 'ot.locale = :locale')
            ->where('p.reviewRequested = 1')
            ->setParameter('locale', $locale)
            ->addOrderBy('p.id', 'ASC')
            ->addOrderBy('pr.position', 'ASC');

        if (count($productCodes) > 0) {
            $qb
                ->andWhere('p.code IN (:productCode)')
                ->setParameter('productCode', $productCodes);
        }
        if (count($questionsAnswered)) {
            $qb
                ->andWhere('pr.id NOT IN (:questionsAnswered)')
                ->setParameter('questionsAnswered', $questionsAnswered);
        }

        return $qb->getQuery()->getResult();
    }

    public function getForFilter(string $localeCode)
    {
        $items = $this->createQueryBuilder('p')
            ->innerJoin('p.translations', 't')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();

        $this->associationHydrator->hydrateAssociations($items, [
            'options',
            'options.translations',
            'translations',
        ]);

        /** @var ProductInterface $item */
        foreach ($items as $item) {
            $item->setCurrentLocale($localeCode);
        }

        return $items;
    }

    public function findAllForBundlesQueryBuilder(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.code LIKE :code')
            ->setParameter('code', '%' . ProductVariantInterface::BUNDLE_PREFIX . '%')
            ->orderBy('p.code', 'ASC')
            ->getQuery()->getResult();
    }

    public function findBySearch(string $term, string $locale): mixed
    {
        return $this->createQueryBuilder('o')
            ->select("CONCAT(translation.name, ' (', o.code, ')') AS name, o.code")
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->addOrderBy('o.updatedAt', 'DESC')
            ->andWhere('o.code LIKE :term')
            ->setParameter('locale', $locale)
            ->setParameter('term', '%' . $term . '%')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function findByNamePart(string $phrase, string $locale, ?int $limit = null): array
    {
        $queryBuilder = $this->createQueryBuilder('o');
        $expressionBuild = $queryBuilder->expr();

        return $queryBuilder
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->andWhere(
                $expressionBuild->orX(
                    'translation.name LIKE :name',
                    'o.code LIKE :name',
                )
            )
            ->setParameter('name', '%' . $phrase . '%')
            ->setParameter('locale', $locale)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
