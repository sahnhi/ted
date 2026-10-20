<?php

declare(strict_types=1);

namespace App\Api\Odoo\StateMachine\Event;

use App\Api\Odoo\Message\Order\CreateOrder;
use App\Api\Odoo\Model\OrderInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Services\Channel\ChannelCode;
use Symfony\Component\Messenger\MessageBusInterface;

final class OnPay
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(Order $order): void
    {
        $orderChannelCode = $order->getChannel()->getCode();

        if (ChannelCode::CHANNEL_RETURN === $orderChannelCode) {
            return;
        }

        if (OrderInterface::ODOO_STATE_CREATED === $order->getOdooState()) {
            return;
        }

        $message = new CreateOrder(orderId: $order->getId());
        $this->messageBus->dispatch($message);
    }
}
