<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\AdminParameter;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class AdminParameterRepository extends EntityRepository
{
    /**
     * @throws NonUniqueResultException
     */
    public function getByKey(string $key): ?AdminParameter
    {
        return $this->createQueryBuilder('ap')
            ->andWhere('ap.key = :key')
            ->setParameter('key', $key)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
