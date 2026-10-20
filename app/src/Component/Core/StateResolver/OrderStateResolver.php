<?php

declare(strict_types=1);

namespace App\Component\Core\StateResolver;

use App\Component\Core\OrderShippingStates;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Order\StateResolver\StateResolverInterface;

final class OrderStateResolver implements StateResolverInterface
{
    public function __construct(
        private readonly FactoryInterface $stateMachineFactory
    ) {
    }

    /**
     * @throws SMException
     */
    public function resolve(BaseOrderInterface $order): void
    {
        $stateMachine = $this->stateMachineFactory->get($order, OrderTransitions::GRAPH);

        if ($this->canOrderBeFulfilled($order) && $stateMachine->can(OrderTransitions::TRANSITION_FULFILL)) {
            $stateMachine->apply(OrderTransitions::TRANSITION_FULFILL);
        }
    }

    private function canOrderBeFulfilled(OrderInterface $order): bool
    {
        return
            OrderPaymentStates::STATE_PAID === $order->getPaymentState()
            && in_array($order->getShippingState(), [OrderShippingStates::STATE_SHIPPED, OrderShippingStates::STATE_DELIVERED])
        ;
    }
}
