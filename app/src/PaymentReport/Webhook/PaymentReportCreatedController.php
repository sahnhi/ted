<?php

declare(strict_types=1);

namespace App\PaymentReport\Webhook;

use Adyen\Util\HmacSignature;
use App\PaymentReport\Command\PaymentReportNotificationCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

final class PaymentReportCreatedController
{
    private const PAYMENT_REPORT = '/^payments_accounting_report_/';

    public function __construct(
        private readonly string $authenticationKey,
        private readonly MessageBusInterface $eventDispatcher
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);
        $notificationRequestItem = $content['notificationItems'][0]['NotificationRequestItem'];

        if (!(new HmacSignature())->isValidNotificationHMAC($this->authenticationKey, $notificationRequestItem)) {
            // this exact body and code is required by Adyen (even if it should be 403 ...)
            return new Response('[accepted]', Response::HTTP_OK);
        }

        if (!preg_match(self::PAYMENT_REPORT, (string) $notificationRequestItem['pspReference'])) {
            return new Response('[accepted]', Response::HTTP_OK);
        }

        $this->eventDispatcher->dispatch(new PaymentReportNotificationCommand($notificationRequestItem));

        // this exact body and code is required by Adyen
        return new Response('[accepted]', Response::HTTP_OK);
    }
}
