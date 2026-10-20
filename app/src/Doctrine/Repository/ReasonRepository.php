<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\OrderItemLogistic;
use App\Doctrine\Entity\Reason;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\OrderInterface;

class ReasonRepository extends EntityRepository implements ReasonRepositoryInterface
{
    public function findForLogisticProductsInOrder(OrderInterface $order): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.products', 'p')
            ->join('p.variants', 'v')
            ->join(OrderItemLogistic::class, 'il', 'with', 'il.productVariantLogistic = v')
            ->join('Sylius:OrderItem', 'i', 'with', 'i.id = il.orderItem')
            ->leftJoin('v.bundleAssociations', 'ba')
            ->where('i.order = :order')
            ->andWhere('r.returnType IN (:returnTypes)')
            ->andWhere('r.enable = true')
            ->setParameter('returnTypes', [Reason::RETURN_TYPE_QUALITY, Reason::RETURN_TYPE_QUALITY_EXCHANGE])
            ->setParameter('order', $order)
            ->getQuery()
            ->getResult();
    }
}
