<?php

declare(strict_types=1);

namespace App\Api\Odoo\StateMachine\Guard;

use App\Services\Channel\ChannelCode;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;

final class DestockOrder
{
    public function __invoke(OrderInterface $order): bool
    {
        if (ChannelCode::CHANNEL_RETURN === $order->getChannel()->getCode()) {
            return false;
        }

        return OrderPaymentStates::STATE_PAID === $order->getPaymentState();
    }
}
