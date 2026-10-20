<?php

namespace App\Services\Mail\Mailer;

use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class PartialPaymentAlert
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws EmailConfigurationNotFoundException
     */
    public function __invoke(OrderInterface $order): void
    {
        $reference = ltrim((string) $order->getNumber(), '0');

        $message = (new Email())
            ->subject("Commande $reference partiellement payée")
            ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
            ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::PARTIALLY_PAID)->getPrincipals())
            ->html("
                Bonjour,\n\n
                Merci de vérifier la commande $reference car elle a été partiellement payée.\n\n
                Cordialement,\n\n
                L'équipe IT
            ", 'text/html');

        $this->mailer->send($message);
    }
}
