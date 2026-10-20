<?php

namespace App\Services\ShippingMethod\Methods;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Services\ShippingMethod\AbstractShippingMethod;

class DefaultShippingMethod extends AbstractShippingMethod
{
    public const NAME = 'Default';

    // The default shipping method has no business logic, therefore this function can stay empty
    protected function handleShippingStockDate(Order $order, Shipment $shipment, ShippingMethod $shippingMethod): void
    {
    }
}
