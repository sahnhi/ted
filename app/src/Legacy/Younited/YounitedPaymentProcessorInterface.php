<?php

namespace App\Legacy\Younited;

use App\Doctrine\Entity\Sylius\Order;

interface YounitedPaymentProcessorInterface
{
    public function activateContract($paymentId);

    public function activateAllByOrder(Order $order);
}
