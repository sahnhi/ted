<?php

namespace App\Doctrine\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class SellerRepository extends EntityRepository implements RepositoryInterface
{
    public function findOneByName(string $name)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('UPPER(o.name) = :name')
            ->setParameter('name', $name)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
