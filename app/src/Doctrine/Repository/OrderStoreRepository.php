<?php

namespace App\Doctrine\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class OrderStoreRepository extends EntityRepository implements RepositoryInterface
{
    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('os')
            ->innerJoin('os.order', 'o')
            ->andWhere('o.channel = 1')  // channel default (web)
        ;
    }

    public function getQuotations($limit, $offset)
    {
        return $this->createQueryBuilder('os')
            ->leftJoin('os.order', 'o')
            ->leftJoin('o.channel', 'c')
            ->where('c.code = :default')
            ->andWhere('o.customer is not null')
            ->setParameter('default', 'default')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()->getResult();
    }
}
