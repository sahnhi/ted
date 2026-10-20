<?php

namespace App\Services\Mail\OutOfStock;

use App\Doctrine\Entity\Sylius\Order;
use App\Message\Mail;
use App\Services\Mail\MailerEmailInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SendEmailToCustomer
{
    /**
     * ShipmentConfirmationReturn constructor.
     */
    public function __construct(
        private readonly MessageBusInterface $bus
    ) {
    }

    public function send(Order $order, \DateTime $date): void
    {
        $mailer = new Mail(MailerEmailInterface::TYPE_OUT_OF_STOCK_CUSTOMER_EMAIL, $order, [
            'recipients' => [
                [
                    'email' => $order->getCustomer()->getEmail(),
                    'firstname' => $order->getCustomer()->getFirstName(),
                    'lastname' => $order->getCustomer()->getLastName(),
                ],
            ],
        ]);
        $mailer->setData([
            'date' => $date,
        ]);

        $this->bus->dispatch($mailer);
    }
}
