<?php

namespace App\Services\Payment;

use App\PaymentReport\Model\PaymentList;

interface PaymentUpdaterInterface
{
    public function updateMultiplePayments(PaymentList $paymentList): void;
}
