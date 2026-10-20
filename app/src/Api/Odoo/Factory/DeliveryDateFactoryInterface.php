<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\DeliveryDateInterface;
use App\Doctrine\Entity\Sylius\ShipmentInterface;

interface DeliveryDateFactoryInterface
{
    public function createNew(ShipmentInterface $shipment): ?DeliveryDateInterface;
}
