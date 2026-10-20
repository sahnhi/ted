<?php

namespace App\Api\Odoo\Model;

use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\TaxRateInterface;

interface ItemInterface extends ObjectMessageOdooInterface, OrderItem
{
    public function __construct(OrderItemInterface $item, ?TaxRateInterface $taxRate = null);
}
