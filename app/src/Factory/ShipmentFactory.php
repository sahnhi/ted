<?php

namespace App\Factory;

use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ShipmentFactory implements ShipmentFactoryInterface
{
    public function __construct(
        private readonly FactoryInterface $decoratedShipmentFactory,
    ) {
    }

    public function createNew(): ShipmentInterface
    {
        /** @var Shipment $shipment */
        $shipment = $this->decoratedShipmentFactory->createNew();

        return $shipment;
    }

    public function createFromExisting(ShipmentInterface $shipment): ShipmentInterface
    {
        $newShipment = $this->createNew();
        $newShipment->setMethod($shipment->getMethod());
        $newShipment->setEndDeliverySlot($shipment->getEndDeliverySlot());
        $newShipment->setStartDeliverySlot($shipment->getStartDeliverySlot());
        $newShipment->setIdShippingMethodBalou($shipment->getIdShippingMethodBalou());
        $newShipment->setShippingDate($shipment->getShippingDate());

        return $newShipment;
    }
}
