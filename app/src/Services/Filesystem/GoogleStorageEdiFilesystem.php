<?php

namespace App\Services\Filesystem;

use App\Doctrine\Entity\Sylius\Order;
use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class GoogleStorageEdiFilesystem extends GoogleStorageFilesystemAbstract
{
    private Order $order;

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): GoogleStorageEdiFilesystem
    {
        $this->order = $order;

        return $this;
    }

    public function moveAndReplace(string $keyFrom, string $keyTo): bool
    {
        if ($this->exists($keyTo)) {
            $this->delete($keyTo);
        }

        return $this->move($keyFrom, $keyTo);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws EmailConfigurationNotFoundException
     */
    protected function sendErrorMail(string $object, string $body): void
    {
        parent::sendErrorMail($object, $body);

        try {
            $spidyEdiAddresses = $this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::SPIDY_EDI);
        } catch (EmailConfigurationNotFoundException) {
            return;
        }

        $supportAddresses = $this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::SUPPORT);

        $message = (new Email())
            ->subject("Erreur lors de la lecture d'un EDI Spidy")
            ->from(new Address($supportAddresses->getFrom(), $supportAddresses->getFromName()))
            ->to(...$spidyEdiAddresses->getPrincipals())
            ->text(sprintf("L'EDI n'a pas pu être récupéré pour la commande %s", $this->order->getNumber()));

        $this->mailer->send($message);
    }
}
