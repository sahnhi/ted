<?php

declare(strict_types=1);

namespace App\PaymentReport\CommandHandler;

use App\Doctrine\Entity\Notification;
use App\PaymentReport\Command\PaymentReportNotificationCommand;
use App\PaymentReport\Event\PaymentReportNotificationReceived;
use App\PaymentReport\Repository\NotificationRepositoryInterface;
use App\Utils\Url;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PaymentReportRequestHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NotificationRepositoryInterface $notificationRepository
    ) {
    }

    public function __invoke(PaymentReportNotificationCommand $command): void
    {
        $paymentReport = $command->getPaymentReportUrl();
        $paymentReportUrl = new Url($paymentReport);

        $reportDate = $command->getPaymentReportDate();

        $notification = new Notification(Notification::ADYEN_PAYMENT_REPORT, $command->getNotificationRequestItem(), new \DateTimeImmutable());
        $this->notificationRepository->save($notification);

        $this->eventDispatcher->dispatch(new PaymentReportNotificationReceived($paymentReportUrl, $reportDate));
    }
}
