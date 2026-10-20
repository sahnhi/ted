<?php

namespace App\Services\Mail\ReturnMail;

use App\Message\Mail;
use App\Services\Mail\MailerEmailInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ShipmentConfirmationReturn
{
    public function __construct(
        private readonly MessageBusInterface $bus
    ) {
    }

    public function send($order): void
    {
        if ('return' === $order->getChannel()->getCode()) {
            $templateOptions = [];
            foreach ($order->getOrderReason() as $orderReason) {
                if ($orderReason->getReason()) {
                    $templateOptions = $orderReason->getReason()->getTemplateOptions() ?? [];
                }
            }
            $mailer = new Mail(MailerEmailInterface::TYPE_RETURN_RECEPTION_EMAIL, $order, [
                'recipients' => [
                    [
                        'email' => $order->getCustomer()->getEmail(),
                        'firstname' => $order->getCustomer()->getFirstName(),
                        'lastname' => $order->getCustomer()->getLastName(),
                    ],
                ],
            ]);
            $mailer->setData([
                'templateOptions' => $templateOptions,
            ]);

            $this->bus->dispatch($mailer);
        }
    }
}
