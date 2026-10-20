<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Sylius\ShippingCategory;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\ChannelInterface;

class ServiceRulesRepository extends EntityRepository
{
    public function findServiceByShippingCategory(string $code): array
    {
        $shippingMethodCategory = $this->createQueryBuilder('s')
            ->join('Sylius:ProductVariant', 'variant')
            ->leftJoin('s.shippingCategory', 'c')
            ->andWhere('variant.code = :code')
            ->andWhere('c.id = variant.shippingCategory')
            ->setParameter('code', $code)
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($shippingMethodCategory as $shippingMethod) {
            $result[] = $shippingMethod['code'];
        }

        return $result;
    }

    public function getServiceRulesByZoneAndChannel(array $zone, ChannelInterface $channel)
    {
        return $this->createQueryBuilder('s')
            ->select('s.priority,
                h.code,
                s.id as service_rules_id,
                warehouse.openingCalendarDays as warehouse_opening_calendarDays,
                warehouse.openingTime as warehouse_opening_time,
                warehouse.closingTime as warehouse_closing_time,
                warehouse.cutOff as warehouse_cut_off,
                h.openingCalendarDays as shipping_opening_calendar_days,
                s.deliverySlot as delivery_slot,
                h.openingTime as shipping_opening_time,
                h.closingTime as shipping_closing_time,
                h.deliveryMessage as shipping_message,
                s.transportTime as transport_time')
            ->addSelect('CASE WHEN (s.shippingMethod is not NULL) THEN 0 ELSE 1 END as isShippingMethod')
            ->addSelect('CASE WHEN (s.idShippingMethodBalou is NULL) THEN h.idShippingMethodBalou ELSE s.idShippingMethodBalou END as id_balou')
            ->join('s.shippingMethod', 'h')
            ->join('s.warehouse', 'warehouse')
            ->join('s.deliveryZone', 'z')
            ->andWhere('z.code IN (:zone)')
            ->andWhere(':channel MEMBER OF h.channels')
            ->setParameter('zone', $zone)
            ->setParameter('channel', $channel)
            ->getQuery()
            ->getResult();
    }

    public function getServiceRulesByShippingCategory(ShippingCategory $shippingCategory, array $serviceRulesId)
    {
        return $this->createQueryBuilder('s')
            ->select('s.priority,
                s.id,
                h.code,
                warehouse.openingCalendarDays as warehouse_opening_calendarDays,
                warehouse.openingTime as warehouse_opening_time,
                warehouse.closingTime as warehouse_closing_time,
                warehouse.cutOff as warehouse_cut_off,
                warehouse.id as warehouse_id,
                h.openingCalendarDays as shipping_opening_calendar_days,
                s.deliverySlot as delivery_slot,
                h.openingTime as shipping_opening_time,
                h.closingTime as shipping_closing_time,
                h.deliveryMessage as shipping_message,
                s.transportTime as transport_time')
            ->addSelect('CASE WHEN (s.shippingMethod is not NULL) THEN 0 ELSE 1 END as isShippingMethod')
            ->addSelect('CASE WHEN (s.idShippingMethodBalou is NULL) THEN h.idShippingMethodBalou ELSE s.idShippingMethodBalou END as id_balou')
            ->join('s.shippingMethod', 'h')
            ->join('s.warehouse', 'warehouse')
            ->leftJoin('s.shippingCategory', 'sc')
            ->andWhere('sc = :shippingCategory')
            ->andWhere('s.id IN (:serviceRulesId)')
            ->setParameter('shippingCategory', $shippingCategory)
            ->setParameter('serviceRulesId', $serviceRulesId)
            ->getQuery()
            ->getResult();
    }
}
