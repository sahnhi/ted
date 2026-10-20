<?php

namespace App\Model\Payum;

use Payum\Core\Model\PaymentInterface as BasePaymentInterface;

/**
 * @method array getDetails()
 */
interface PaymentInterface extends BasePaymentInterface
{
    /**
     * @return object|null
     */
    public function getOrder();

    /**
     * @param object $order
     *
     * @return void
     */
    public function setOrder($order);
}
