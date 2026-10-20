<?php

declare(strict_types=1);

namespace App\PaymentReport\Subscriber;

use App\PaymentReport\Command\DownloadReportCommand;
use App\PaymentReport\Event\PaymentReportNotificationReceived;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class PaymentReportNotificationReceivedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $bus
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            PaymentReportNotificationReceived::class => ['downloadPaymentReport'],
        ];
    }

    public function downloadPaymentReport(PaymentReportNotificationReceived $event): void
    {
        $this->bus->dispatch(new DownloadReportCommand($event->getPaymentReportPath()));
    }
}
