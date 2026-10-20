<?php

namespace App\Services\Shipment;

use App\Business\Command\ChooseShipping;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Services\ShippingMethod\ShippingMethodService;

class ShipmentService
{
    public function __construct(
        private readonly ShippingMethodService $shippingMethodService
    ) {
    }

    public function chooseShippingMethod(
        Order $order,
        Shipment $shipment,
        ShippingMethod $shippingMethod,
        ChooseShipping $chooseShipping
    ): void {
        $shipment->setMethod($shippingMethod);
        $shipment->setStartDeliverySlot($chooseShipping->getStartDeliverySlot());
        $shipment->setEndDeliverySlot($chooseShipping->getEndDeliverySlot());
        $shipment->setIdShippingMethodBalou($chooseShipping->getIdShippingMethodBalou());

        $this->shippingMethodService->chooseShippingMethod($order, $shipment, $shippingMethod, $chooseShipping);
    }
}
