<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\Refund;
use App\Api\Odoo\Model\RefundInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class RefundFactory implements RefundFactoryInterface
{
    public function createNew(PaymentInterface $syliusPayment): RefundInterface
    {
        return new Refund($syliusPayment);
    }
}
