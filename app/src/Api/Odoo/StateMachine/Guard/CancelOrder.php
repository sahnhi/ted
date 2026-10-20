<?php

declare(strict_types=1);

namespace App\Api\Odoo\StateMachine\Guard;

use App\Api\Odoo\Model\Order;
use App\Doctrine\Entity\Sylius\OrderInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;

final class CancelOrder
{
    public function __invoke(OrderInterface $order): bool
    {
        if (in_array($order->getOdooState(), [
            Order::ODOO_STATE_READY,
            Order::ODOO_STATE_NOT_READY,
        ], true)) {
            return false;
        }

        return BaseOrderInterface::STATE_CANCELLED === $order->getState();
    }
}
