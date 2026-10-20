<?php

declare(strict_types=1);

namespace App\EventSubscriber\Payment;

use App\PaymentReport\Event\PaymentReportDownloaded;
use App\Services\Payment\PaymentUpdaterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PaymentReportDownloadedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PaymentUpdaterInterface $paymentUpdaterHandler
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            PaymentReportDownloaded::class => ['updatePayment'],
        ];
    }

    public function updatePayment(PaymentReportDownloaded $paymentReportDownloaded): void
    {
        $this->paymentUpdaterHandler->updateMultiplePayments($paymentReportDownloaded->getPaymentReport());
    }
}
