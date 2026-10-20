<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\View\ShippingMethodTransmissionView;
use App\View\ShippingMethodView;
use Sylius\Component\Core\Model\ShipmentInterface;

interface ShippingMethodViewFactoryInterface
{
    public function create(ShipmentInterface $shipment, string $locale, string $currency): ShippingMethodView;

    public function createWithShippingMethod(ShipmentInterface $shipment, ShippingMethodInterface $shippingMethod, string $locale, string $currency): ShippingMethodView;

    public function createTransmission(ShipmentInterface $shipment, ShippingMethodInterface $shippingMethod, string $locale, string $currency): ShippingMethodTransmissionView;
}
