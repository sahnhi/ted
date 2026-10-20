<?php

namespace App\ViewRepository;

use App\View\OrderTransmissionView;

interface OrderTransmissionViewRepositoryInterface
{
    public function getOneById(string $orderId): OrderTransmissionView;
}
