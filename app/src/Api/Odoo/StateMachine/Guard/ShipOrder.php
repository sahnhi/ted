<?php

declare(strict_types=1);

namespace App\Api\Odoo\StateMachine\Guard;

use App\Api\Odoo\Model\OrderInterface as OdooOrderInterface;
use App\Doctrine\Entity\Sylius\OrderInterface;

final class ShipOrder
{
    public function __invoke(OrderInterface $order): bool
    {
        return OdooOrderInterface::ODOO_STATE_CREATED === $order->getOdooState();
    }
}
