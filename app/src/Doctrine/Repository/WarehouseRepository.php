<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Warehouse;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class WarehouseRepository extends EntityRepository implements WarehouseRepositoryInterface
{
    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findDefault(): ?Warehouse
    {
        return $this->createQueryBuilder('w')
            ->where('w.name = :default')
            ->setParameter('default', Warehouse::WAREHOUSE_DEFAULT)
            ->getQuery()->getSingleResult();
    }
}
