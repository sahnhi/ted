<?php

declare(strict_types=1);

namespace App\PaymentReport\Event;

use App\Utils\Url;

final class PaymentReportNotificationReceived
{
    /** @var \DateTimeImmutable */
    private $eventDate;

    public function __construct(
        private readonly Url $paymentReportPath,
        private readonly \DateTimeImmutable $reportDate
    ) {
        $this->eventDate = new \DateTimeImmutable();
    }

    public function getPaymentReportPath(): Url
    {
        return $this->paymentReportPath;
    }

    public function getReportDate(): \DateTimeImmutable
    {
        return $this->reportDate;
    }

    public function getEventDate(): \DateTimeImmutable
    {
        return $this->eventDate;
    }
}
