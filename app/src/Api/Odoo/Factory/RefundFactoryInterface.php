<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\RefundInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface RefundFactoryInterface
{
    public function createNew(PaymentInterface $syliusPayment): RefundInterface;
}
