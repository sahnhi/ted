<?php

declare(strict_types=1);

namespace App\PaymentReport\Command;

use Webmozart\Assert\Assert;

final class PaymentReportNotificationCommand
{
    /** @var string */
    private $paymentReportUrl;
    /** @var \DateTimeImmutable */
    private $paymentReportDate;
    /** @var string */
    private $notificationRequestItem;

    public function __construct(array $notificationRequestItem)
    {
        Assert::stringNotEmpty($notificationRequestItem['reason']);
        Assert::stringNotEmpty($notificationRequestItem['eventDate']);

        $this->paymentReportUrl = $notificationRequestItem['reason'];
        $this->paymentReportDate = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $notificationRequestItem['eventDate']);
        $this->notificationRequestItem = json_encode($notificationRequestItem, JSON_THROW_ON_ERROR);
    }

    public function getPaymentReportUrl(): string
    {
        return $this->paymentReportUrl;
    }

    public function getPaymentReportDate(): \DateTimeImmutable
    {
        return $this->paymentReportDate;
    }

    public function getNotificationRequestItem(): string
    {
        return $this->notificationRequestItem;
    }
}
