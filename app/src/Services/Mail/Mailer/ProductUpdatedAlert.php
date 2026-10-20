<?php

namespace App\Services\Mail\Mailer;

use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistryInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class ProductUpdatedAlert
{
    public const TYPE_PRODUCT = 'produit';
    public const TYPE_VARIANT = 'variant';

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws EmailConfigurationNotFoundException
     */
    public function generateEmail(string $type, string $code, string $adminUser): void
    {
        $message = (new Email())
            ->subject('Modification du ' . $type . ' - Référence ' . $code)
            ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
            ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::PRODUCT_CHANGE)->getPrincipals())
            ->html(
                'Bonjour<br/><br/>
                Le ' . $type . ' suivant a été modifié à ' . date_format(new \DateTime(), 'H:i \l\e d/m/Y') . ' par '
                . $adminUser
                . ' :<br/>
                - ' . $code . '<br/><br/>
                Cordialement,<br/>
                Tediber',
                'text/html'
            );

        $this->mailer->send($message);
    }
}
