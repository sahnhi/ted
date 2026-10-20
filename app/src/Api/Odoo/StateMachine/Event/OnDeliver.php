<?php

declare(strict_types=1);

namespace App\Api\Odoo\StateMachine\Event;

use App\Api\Odoo\Message\Return\ConfirmReturn;
use App\Api\Odoo\Model\OrderInterface;
use App\Doctrine\Entity\Sylius\Order;
use SM\Factory\FactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class OnDeliver
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly FactoryInterface $stateMachineFactory,
    ) {
    }

    public function __invoke(Order $order): void
    {
        $stateMachine = $this->stateMachineFactory->get($order, \App\Api\Odoo\Model\Order::GRAPH);

        if ($stateMachine->can(OrderInterface::ODOO_TRANSITION_CONFIRM_RETURN)) {
            $message = new ConfirmReturn(orderId: $order->getId());
        }

        if (!isset($message)) {
            return;
        }

        $this->messageBus->dispatch($message);
    }
}
