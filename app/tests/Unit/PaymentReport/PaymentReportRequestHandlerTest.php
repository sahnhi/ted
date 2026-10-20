<?php

declare(strict_types=1);

namespace App\Tests\Unit\PaymentReport;

use App\PaymentReport\Command\PaymentReportNotificationCommand;
use App\PaymentReport\CommandHandler\PaymentReportRequestHandler;
use App\PaymentReport\Repository\NotificationRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class PaymentReportRequestHandlerTest extends TestCase
{
    public function testEventIsDispatched(): void
    {
        $bus = $this->createMock(EventDispatcherInterface::class);
        $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);

        $command = new PaymentReportNotificationCommand(
            [
                'reason' => 'https://google.com',
                'eventDate' => '2022-11-07T16:11:13+01:00',
            ]
        );

        $notificationRepository->expects(self::once())
            ->method('save');

        $bus->expects(self::once())
            ->method('dispatch');

        $paymentReportRequestHandler = new PaymentReportRequestHandler($bus, $notificationRepository);
        $paymentReportRequestHandler->__invoke($command);
    }
}
