<?php

namespace App\Model\BalouImport;

use App\Doctrine\Entity\OrderReason;
use Sylius\Component\Payment\Model\PaymentInterface;

class BalouOrderReturnItem
{
    /**
     * @var BalouOrder
     */
    public $order;

    /**
     * @var PaymentInterface|null
     */
    public $refund;

    /**
     * @var OrderReason|null
     */
    public $refundReason;

    /**
     * @var bool
     */
    public $wrapped;
}
