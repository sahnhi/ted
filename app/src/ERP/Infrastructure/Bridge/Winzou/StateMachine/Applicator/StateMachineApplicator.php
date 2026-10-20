<?php

declare(strict_types=1);

namespace App\ERP\Infrastructure\Bridge\Winzou\StateMachine\Applicator;

use App\Api\Odoo\Model\OrderInterface;
use App\Doctrine\Entity\Sylius\Order;
use SM\Factory\FactoryInterface;
use SM\SMException;

final class StateMachineApplicator
{
    public function __construct(
        private readonly FactoryInterface $stateMachineFactory,
    ) {
    }

    /**
     * @throws SMException
     */
    public function cancelOrder(Order $order): void
    {
        $orderStateMachine = $this->stateMachineFactory->get($order, OrderInterface::GRAPH);
        if ($orderStateMachine->can(OrderInterface::ODOO_TRANSITION_CANCEL)) {
            $orderStateMachine->apply(OrderInterface::ODOO_TRANSITION_CANCEL);
        }
    }

    /**
     * @throws SMException
     */
    public function confirmReturn(Order $order): void
    {
        $orderStateMachine = $this->stateMachineFactory->get($order, OrderInterface::GRAPH);
        if ($orderStateMachine->can(OrderInterface::ODOO_TRANSITION_CONFIRM_RETURN)) {
            $orderStateMachine->apply(OrderInterface::ODOO_TRANSITION_CONFIRM_RETURN);
        }
    }

    /**
     * @throws SMException
     */
    public function createOrder(Order $order): void
    {
        $orderStateMachine = $this->stateMachineFactory->get($order, OrderInterface::GRAPH);
        if ($orderStateMachine->can(OrderInterface::ODOO_TRANSITION_CREATE)) {
            $orderStateMachine->apply(OrderInterface::ODOO_TRANSITION_CREATE);
        }
    }

    /**
     * @throws SMException
     */
    public function createReturn(Order $order): void
    {
        $orderStateMachine = $this->stateMachineFactory->get($order, OrderInterface::GRAPH);
        if ($orderStateMachine->can(OrderInterface::ODOO_TRANSITION_CREATE_RETURN)) {
            $orderStateMachine->apply(OrderInterface::ODOO_TRANSITION_CREATE_RETURN);
        }
    }

    /**
     * @throws SMException
     */
    public function shipOrder(Order $order): void
    {
        $orderStateMachine = $this->stateMachineFactory->get($order, OrderInterface::GRAPH);
        if ($orderStateMachine->can(OrderInterface::ODOO_TRANSITION_SHIP)) {
            $orderStateMachine->apply(OrderInterface::ODOO_TRANSITION_SHIP);
        }
    }

    /**
     * @throws SMException
     */
    public function destockOrder(Order $order): void
    {
        $orderStateMachine = $this->stateMachineFactory->get($order, OrderInterface::GRAPH);
        if ($orderStateMachine->can(OrderInterface::ODOO_TRANSITION_DESTOCK)) {
            $orderStateMachine->apply(OrderInterface::ODOO_TRANSITION_DESTOCK);
        }
    }
}
