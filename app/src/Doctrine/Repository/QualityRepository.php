<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Quality;
use App\Doctrine\Entity\Sylius\Order;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

/**
 * @method Quality|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quality|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quality[]    findAll()
 * @method Quality[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QualityRepository extends EntityRepository
{
    /**
     * @return float|int|mixed|string
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countForOrder(Order $order)
    {
        $qb = $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->innerJoin('q.orderItemLogistic', 'oil')
            ->innerJoin('oil.orderItem', 'oi')
            ->andWhere('oi.order = :order')
            ->setParameter('order', $order);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findNewFirst(): QueryBuilder
    {
        return $this->createQueryBuilder('q')
            ->select('q')
            ->orderBy('case when q.status = \'new\' then 1 else 2 end', 'ASC');
    }
}
