<?php

declare(strict_types=1);

namespace App\Api\Odoo\StateMachine\Event;

use App\Api\Odoo\Message\Order\ConfirmOrder;
use App\Doctrine\Entity\Sylius\Order;
use App\Services\Channel\ChannelCode;
use Symfony\Component\Messenger\MessageBusInterface;

final class OnShip
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(Order $order): void
    {
        $orderChannelCode = $order->getChannel()->getCode();

        if (ChannelCode::CHANNEL_RETURN !== $orderChannelCode) {
            $message = new ConfirmOrder(orderId: $order->getId());
        }

        if (!isset($message)) {
            return;
        }

        $this->messageBus->dispatch($message);
    }
}
