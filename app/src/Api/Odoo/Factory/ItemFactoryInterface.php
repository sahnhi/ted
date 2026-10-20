<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\ItemInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

interface ItemFactoryInterface
{
    public function createNew(OrderItemInterface $syliusItem): ItemInterface;
}
