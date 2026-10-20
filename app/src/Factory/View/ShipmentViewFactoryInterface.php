<?php

namespace App\Factory\View;

use App\View\ShipmentTransmissionView;
use App\View\ShipmentView;
use Sylius\Component\Core\Model\ShipmentInterface;

interface ShipmentViewFactoryInterface
{
    public function create(ShipmentInterface $shipment, string $locale): ShipmentView;

    public function createTransmission(ShipmentInterface $shipment, string $locale): ShipmentTransmissionView;
}
