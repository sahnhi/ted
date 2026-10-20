<?php

namespace App\StateMachine;

use App\Doctrine\Entity\Sylius\Order;

interface StateMachineApplicatorInterface
{
    public function cancelOrder(Order $order): void;
}
