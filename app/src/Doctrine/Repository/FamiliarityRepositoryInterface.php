<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Familiarity;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Familiarity[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface FamiliarityRepositoryInterface
{
    public function createQueryBuilderByLocaleEnabled(string $locale): QueryBuilder;

    public function findOneByCode(string $code): Familiarity|null;
}
