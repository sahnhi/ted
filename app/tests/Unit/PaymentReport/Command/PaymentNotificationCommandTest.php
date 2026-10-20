<?php

declare(strict_types=1);

namespace App\Tests\Unit\PaymentReport\Command;

use App\PaymentReport\Command\PaymentReportNotificationCommand;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

final class PaymentNotificationCommandTest extends TestCase
{
    public function testCommandIsValid(): void
    {
        $command = new PaymentReportNotificationCommand(
            [
                'reason' => 'https://google.com',
                'eventDate' => '2022-11-07T16:11:13+01:00',
            ]
        );
        $this->assertEquals('https://google.com', $command->getPaymentReportUrl());
        $this->assertEquals(\DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2022-11-07T16:11:13+01:00'), $command->getPaymentReportDate());
    }

    public function testReportArgumentIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a different value than ""');

        new PaymentReportNotificationCommand(
            [
                'reason' => '',
                'eventDate' => '2022-11-07T16:11:13+01:00',
            ]
        );
    }

    public function testReportArgumentTypeIsWrong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a string. Got: integer');

        new PaymentReportNotificationCommand(
            [
                'reason' => 'myReportIsAvailable',
                'eventDate' => 12,
            ]
        );
    }
}
