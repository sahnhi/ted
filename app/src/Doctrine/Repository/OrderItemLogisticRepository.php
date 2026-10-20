<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\OrderItemLogistic;
use App\Doctrine\Entity\Sylius\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Core\Model\OrderInterface;

class OrderItemLogisticRepository extends ServiceEntityRepository implements OrderItemLogisticRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItemLogistic::class);
    }

    /**
     * The complex leftJoin in the request is made in order to retrieve all the entities
     * form OrderItemLogistic that are not already present in Quality.
     *
     * @return float|int|mixed|string
     */
    public function findForOrderAndNotLinked(Order $order)
    {
        $builder = $this->createQueryBuilder('oil')
            ->select('oil')
            ->innerJoin('oil.orderItem', 'oi')
            ->leftJoin(
                'oil.quality',
                'q',
                Join::WITH,
                'q.orderItemLogistic = oil.id'
            )->andWhere('oi.order = :order')
            ->andWhere('q IS NULL')
            ->setParameter('order', $order);

        return $builder->getQuery()->getResult();
    }

    public function findByOrder(OrderInterface $order): array
    {
        return $this->createQueryBuilder('oil')
            ->leftJoin('oil.orderItem', 'oi')
            ->where('oi.order = :order')
            ->setParameters([
                'order' => $order,
            ])
            ->getQuery()
            ->getResult();
    }
}
