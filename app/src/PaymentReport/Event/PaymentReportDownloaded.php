<?php

declare(strict_types=1);

namespace App\PaymentReport\Event;

use App\PaymentReport\Model\PaymentList;

final class PaymentReportDownloaded
{
    public function __construct(
        private readonly PaymentList $paymentReport
    ) {
    }

    public function getPaymentReport(): PaymentList
    {
        return $this->paymentReport;
    }
}
