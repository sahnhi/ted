<?php

namespace App\Services\ShippingMethod;

use App\Business\Command\ChooseShipping;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;

interface ShippingMethodInterface
{
    public function chooseShippingMethod(Order $order, Shipment $shipment, ShippingMethod $shippingMethod, ChooseShipping $chooseShipping): void;
}
