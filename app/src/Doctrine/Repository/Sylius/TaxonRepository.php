<?php

namespace App\Doctrine\Repository\Sylius;

use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository as BaseTaxonRepository;
use Sylius\Component\Core\Model\Taxon;

class TaxonRepository extends BaseTaxonRepository implements TaxonRepositoryInterface
{
    public function findAllByLocale(string $locale, ?array $slugs = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->addSelect('translation')
            ->innerJoin('t.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->addOrderBy('t.createdAt', 'DESC')
            ->setParameter('locale', $locale);

        if (!empty($slugs)) {
            $qb->andWhere('translation.slug IN (:slugs)')
                ->setParameter('slugs', $slugs);
        }

        return $qb->getQuery()->getResult();
    }

    public function findOneByLocaleAndSlug(string $locale, string $slug): ?Taxon
    {
        $qb = $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->andWhere('translation.slug = :slug')
            ->setParameter('locale', $locale)
            ->setParameter('slug', $slug);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllEnabledByLocale(string $locale): array
    {
        $qb = $this->createQueryBuilder('t')
            ->addSelect('translation')
            ->innerJoin('t.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->where('t.code NOT LIKE :review')
            ->andWhere('t.code NOT LIKE :return')
            ->addOrderBy('t.createdAt', 'DESC')
            ->setParameter('review', 'review_%')
            ->setParameter('return', 'return%')
            ->setParameter('locale', $locale);

        return $qb->getQuery()->getResult();
    }
}
