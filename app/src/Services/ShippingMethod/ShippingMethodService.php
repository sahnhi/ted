<?php

namespace App\Services\ShippingMethod;

use App\Business\Command\ChooseShipping;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;

class ShippingMethodService
{
    public function __construct(
        private readonly ShippingMethodFactory $shippingMethodFactory
    ) {
    }

    public function chooseShippingMethod(
        Order $order,
        Shipment $shipment,
        ShippingMethod $shippingMethod,
        ChooseShipping $chooseShipping
    ): void {
        $this->shippingMethodFactory
            ->getFromShippingMethod($shippingMethod)
            ->chooseShippingMethod($order, $shipment, $shippingMethod, $chooseShipping);
    }
}
