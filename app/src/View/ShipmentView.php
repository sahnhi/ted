<?php

namespace App\View;

class ShipmentView
{
    public string $state;
    public ?\DateTime $startDeliverySlot = null;
    public ?\DateTime $endDeliverySlot = null;
    public ShippingMethodView $method;
    public ?string $tracking = null;

    public function __construct()
    {
        $this->method = new ShippingMethodView();
    }
}
