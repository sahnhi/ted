<?php

declare(strict_types=1);

namespace App\StateMachine;

use App\Doctrine\Entity\Sylius\Order;
use SM\Factory\FactoryInterface;
use Sylius\Component\Order\OrderTransitions;

class StateMachineApplicator implements StateMachineApplicatorInterface
{
    public function __construct(
        private readonly FactoryInterface $stateMachineFactory
    ) {
    }

    public function cancelOrder(Order $order): void
    {
        $this->stateMachineFactory->get($order, OrderTransitions::GRAPH)->apply(OrderTransitions::TRANSITION_CANCEL);
    }
}
