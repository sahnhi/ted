<?php

namespace App\Api\Odoo\Model;

use Sylius\Component\Core\Model\PaymentInterface;

interface RefundInterface extends ObjectMessageOdooInterface
{
    public function __construct(PaymentInterface $payment);
}
