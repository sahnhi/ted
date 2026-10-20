<?php

declare(strict_types=1);

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\ShipmentItem;
use App\Doctrine\Entity\Sylius\OrderInterface;

interface ShipmentItemFactoryInterface
{
    public function createNew(OrderInterface $syliusOrder): ShipmentItem;
}
