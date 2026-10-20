<?php

namespace App\Services\Calendar\Maker;

interface DeliverySlotMakerInterface
{
    public function makeDeliverySlot($shippingMethod, $nbrOfDay): array;
}
