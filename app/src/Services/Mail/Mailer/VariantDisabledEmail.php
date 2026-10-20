<?php

namespace App\Services\Mail\Mailer;

use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistryInterface;
use Sylius\Component\Core\Model\AdminUser;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class VariantDisabledEmail
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry
    ) {
    }

    /**
     * @throws EmailConfigurationNotFoundException
     * @throws TransportExceptionInterface
     */
    public function generateEmail(string $variantCode, AdminUser $adminUser): void
    {
        $message = (new Email())
            ->subject('Désactivation produit ' . $variantCode)
            ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
            ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::VARIANT_DISABLED_EMAIL)->getPrincipals())
            ->html(
                'Bonjour<br/><br/>
                Le produit suivant a été désactivé par '
                . $adminUser->getFirstName()
                . ' ' . $adminUser->getLastName()
                . ' - ' . $adminUser->getEmail()
                . ' :<br/>
                - ' . $variantCode . '<br/><br/>
                Cordialement,<br/>
                Tediber',
                'text/html'
            );

        $this->mailer->send($message);
    }
}
