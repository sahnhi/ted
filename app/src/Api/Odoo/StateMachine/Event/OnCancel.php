<?php

declare(strict_types=1);

namespace App\Api\Odoo\StateMachine\Event;

use App\Api\Odoo\Message\Cancel as CancelMessage;
use App\Doctrine\Entity\Sylius\Order;
use Symfony\Component\Messenger\MessageBusInterface;

final class OnCancel
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(Order $order): void
    {
        $message = new CancelMessage(orderId: $order->getId());
        $this->messageBus->dispatch($message);
    }
}
