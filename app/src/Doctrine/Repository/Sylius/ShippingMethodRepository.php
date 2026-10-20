<?php

namespace App\Doctrine\Repository\Sylius;

use Doctrine\ORM\NonUniqueResultException;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ShippingMethodRepository as BaseShippingMethodRepository;

class ShippingMethodRepository extends BaseShippingMethodRepository
{
    /**
     * @return float|int|mixed|string
     */
    public function getByShippingCode($shippingCode, $methodCodes)
    {
        return $this->createQueryBuilder('s')
            ->select(
                's.position as priority,
                s.id,
                s.code,
                s.pickUpPoint,
                warehouse.openingCalendarDays as warehouse_opening_calendarDays,
                warehouse.openingTime as warehouse_opening_time,
                warehouse.closingTime as warehouse_closing_time,
                warehouse.cutOff as warehouse_cut_off,
                warehouse.id as warehouse_id,
                s.openingCalendarDays as shipping_opening_calendar_days,
                s.timeSlot as delivery_slot,
                s.openingTime as shipping_opening_time,
                s.closingTime as shipping_closing_time,
                s.idShippingMethodBalou as id_balou,
                s.transportTime as transport_time,
                s.deliveryMessage as shipping_message,
                s.calendar'
            )
            ->addSelect('CASE WHEN (s.id > 0) THEN 1 ELSE 0 END as isShippingMethod')
            ->leftJoin('s.warehouse', 'warehouse')
            ->join('Sylius:ProductVariant', 'variant')
            ->leftJoin('s.shippingCategory', 'c')
            ->andWhere('variant.code = :code')
            ->andWhere('c.id = variant.shippingCategory')
            ->andWhere('s.code IN (:methodeCodes)')
            ->setParameter('code', $shippingCode)
            ->setParameter('methodeCodes', $methodCodes)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByCode(string $code, ?string $channelCode = null): ?array
    {
        $qb = $this->createQueryBuilder('s')
            ->select(
                's.position as priority,
            s.code,
            s.pickUpPoint,
            warehouse.openingCalendarDays as warehouse_opening_calendarDays,
            warehouse.openingTime as warehouse_opening_time,
            warehouse.closingTime as warehouse_closing_time,
            warehouse.cutOff as warehouse_cut_off,
            s.openingCalendarDays as shipping_opening_calendar_days,
            s.timeSlot as delivery_slot,
            s.openingTime as shipping_opening_time,
            s.closingTime as shipping_closing_time,
            s.idShippingMethodBalou as id_balou,
            s.transportTime as transport_time,
            s.deliveryMessage as shipping_message,
            s.calendar'
            )
            ->addSelect('CASE WHEN (s.id > 0) THEN 1 ELSE 0 END as isShippingMethod')
            ->leftJoin('s.warehouse', 'warehouse')
            ->andWhere('s.code = :code')
            ->setParameter('code', $code);

        if (null !== $channelCode) {
            $qb
                ->innerJoin('s.channels', 'channel')
                ->andWhere('channel.code = :channel')
                ->setParameter('channel', $channelCode);
        }

        return $qb->getQuery()
            ->getOneOrNullResult();
    }
}
