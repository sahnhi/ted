<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\Order;
use App\View\OrderTransmissionView;

interface OrderTransmissionViewFactoryInterface
{
    public function create(Order $order, string $localeCode): OrderTransmissionView;
}
