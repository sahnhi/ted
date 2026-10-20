<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Familiarity;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

/**
 * @method Familiarity[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FamiliarityRepository extends EntityRepository implements FamiliarityRepositoryInterface
{
    public function createQueryBuilderByLocaleEnabled(string $locale): QueryBuilder
    {
        return $this->createQueryBuilder('f')
            ->addSelect('translation')
            ->innerJoin('f.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->where('f.enabled = :enabled')
            ->setParameter('locale', $locale)
            ->setParameter('enabled', true);
    }

    public function findOneByCode(string $code): ?Familiarity
    {
        return $this->findOneBy(['code' => $code]);
    }
}
