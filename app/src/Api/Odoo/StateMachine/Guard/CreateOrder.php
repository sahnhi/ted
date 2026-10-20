<?php

declare(strict_types=1);

namespace App\Api\Odoo\StateMachine\Guard;

use App\Api\Odoo\Model\OrderInterface as OdooOrderInterface;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Services\Channel\ChannelCode;
use Sylius\Component\Core\OrderPaymentStates;

final class CreateOrder
{
    public function __invoke(OrderInterface $order): bool
    {
        if (OdooOrderInterface::ODOO_STATE_CREATED === $order->getOdooState()) {
            return false;
        }

        if (ChannelCode::CHANNEL_RETURN === $order->getChannel()->getCode()) {
            return false;
        }

        return OrderPaymentStates::STATE_PAID === $order->getPaymentState();
    }
}
