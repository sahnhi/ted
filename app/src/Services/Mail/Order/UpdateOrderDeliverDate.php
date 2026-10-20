<?php

declare(strict_types=1);

namespace App\Services\Mail\Order;

use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistryInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class UpdateOrderDeliverDate implements UpdateOrderDeliverDateInterface
{
    public function __construct(
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry,
        private readonly MailerInterface $mailer
    ) {
    }

    /**
     * @throws EmailConfigurationNotFoundException
     * @throws TransportExceptionInterface
     */
    public function notifyNewDate(string $commandNumber, \DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        $body = sprintf("Le créneau de livraison de la commande %s a été modifié. La nouvelle date de livraison à prendre en compte est : <br /><br >
                 - début: %s <br /><br />
                 - fin: %s<br /><br />
                 Merci de bien le prendre en compte.<br /><br />
                 L'équipe Tediber", $commandNumber, $startDate->format('d/m/Y H:i:s'), $endDate->format('d/m/Y H:i:s'));

        $message = (new Email())
            ->subject("Modification créneau de livraison - Commande:  $commandNumber")
            ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
            ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::UPDATE_ORDER_DELIVERY_DATE)->getPrincipals())
            ->html($body);

        $this->mailer->send($message);
    }
}
