<?php

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\ShippingMethodGroup;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use Doctrine\ORM\Query\Expr\Join;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ShipmentRepository as BaseShipmentRepository;
use Sylius\Component\Core\OrderPaymentStates;

class ShipmentRepository extends BaseShipmentRepository implements ShipmentRepositoryInterface
{
    public function findOneByOrderNumber(string $orderNumber): ?ShipmentInterface
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.order', 'o')
            ->where('o.number = :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLimitsReached(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select("m.id, COUNT(s.id) as number,
             CASE WHEN m.maxPerSlot >= smg.maxPerSlot THEN m.maxPerSlot ELSE smg.maxPerSlot END as max_per_slot,
             DATE_FORMAT(s.startDeliverySlot, '%Y-%m-%d') as date_limit")
            ->innerJoin('s.method', 'm')
            ->innerJoin(ShippingMethodGroup::class, 'smg', Join::WITH, 'm.shippingMethodGroup = smg.id')
            ->innerJoin('s.order', 'o')
            ->where('s.startDeliverySlot > :now')
            ->andWhere('o.paymentState = :paid')
            ->groupBy('m.id, smg.maxPerDay, date_limit')
            ->having('number >= max_per_slot')
            ->setParameter('paid', OrderPaymentStates::STATE_PAID)
            ->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }

    public function findLimitsPerSlotReached(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select("m.id, COUNT(s.id) as number,
                CASE WHEN m.maxPerSlot >= smg.maxPerSlot THEN m.maxPerSlot ELSE smg.maxPerSlot END as max_per_slot,
                DATE_FORMAT(s.startDeliverySlot, '%Y-%m-%d %H:%i:%s') as date_limit_start,
                DATE_FORMAT(s.endDeliverySlot, '%Y-%m-%d %H:%i:%s') as date_limit_end")
            ->innerJoin('s.method', 'm')
            ->innerJoin(ShippingMethodGroup::class, 'smg', Join::WITH, 'm.shippingMethodGroup = smg.id')
            ->innerJoin('s.order', 'o')
            ->where('s.startDeliverySlot > :now')
            ->andWhere('o.paymentState = :paid')
            ->groupBy('m.id, date_limit_start, date_limit_end')
            ->having('number >= max_per_slot')
            ->setParameter('paid', OrderPaymentStates::STATE_PAID)
            ->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }

    public function getByOrder(int $orderId): ?Shipment
    {
        return $this->findOneBy(['order' => $orderId]);
    }
}
