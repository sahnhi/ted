<?php

declare(strict_types=1);

namespace App\Tests\Unit\PaymentReport\Event;

use App\PaymentReport\Event\PaymentReportNotificationReceived;
use App\Utils\Url;
use PHPUnit\Framework\TestCase;

final class PaymentReportNotificationReceivedTest extends TestCase
{
    public function testCommandIsValid(): void
    {
        $command = new PaymentReportNotificationReceived(
            new Url('https://www.tediber.com'),
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2022-11-07T16:11:13+01:00'),
        );

        $this->assertEquals('https://www.tediber.com', $command->getPaymentReportPath());
        $this->assertEquals(\DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2022-11-07T16:11:13+01:00'), $command->getReportDate());
    }
}
