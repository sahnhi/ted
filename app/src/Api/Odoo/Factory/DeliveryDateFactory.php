<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\DeliveryDate;
use App\Api\Odoo\Model\DeliveryDateInterface;
use App\Doctrine\Entity\Sylius\ShipmentInterface;

class DeliveryDateFactory implements DeliveryDateFactoryInterface
{
    public function __construct(
        private readonly int $timeInterval
    ) {
    }

    /**
     * @throws \Exception
     */
    public function createNew(ShipmentInterface $shipment): ?DeliveryDateInterface
    {
        $start = $shipment->getStartDeliverySlot();
        $end = $shipment->getEndDeliverySlot();
        // Todo: to remove when the php -2 hours will be fixed
        $interval = new \DateInterval('PT' . $this->timeInterval . 'H');
        if (empty($start) || empty($end)) {
            return null;
        }
        $start->sub($interval);
        $end->sub($interval);

        return new DeliveryDate($start, $end);
    }
}
