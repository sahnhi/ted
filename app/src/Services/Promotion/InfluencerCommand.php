<?php

namespace App\Services\Promotion;

use App\Doctrine\Entity\Sylius\Order;
use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistryInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class InfluencerCommand
{
    public const PROMOTION_INFLUENCER_KEYWORD = 'influence';

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry
    ) {
    }

    /**
     * @throws EmailConfigurationNotFoundException
     * @throws TransportExceptionInterface
     */
    public function checkPromotion(Order $order): void
    {
        if (!$this->checkCode($order)) {
            return;
        }

        $body = $order->getCustomer()->getFullName() . ' a effectué une commande #' . $order->getNumber() . ' le ' . $order->getCheckoutCompletedAt()->format('d/m/Y') .
        ' avec une livraison prévue pour le ' . $order->getShipments()->first()->getStartDeliverySlot()->format('d/m/Y') . "<br /><br >
        Cordialement,<br /><br />
        L'équipe IT";

        $message = (new Email())
            ->subject($order->getNumber() . ' commandée')
            ->from(new Address('dev@tediber.com', 'TEDIBER'))
            ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::INFLUENCER_ORDER)->getPrincipals())
            ->html($body);

        $this->mailer->send($message);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws EmailConfigurationNotFoundException
     */
    public function checkDelivered(Order $order): void
    {
        if (!$this->checkCode($order)) {
            return;
        }

        $body = $order->getCustomer()->getFullName() . ' a reçu sa commande #' . $order->getNumber() . ' commandée le ' . $order->getCheckoutCompletedAt()->format('d/m/Y') .
            ' avec une livraison prévue pour le ' . $order->getShipments()->first()->getStartDeliverySlot()->format('d/m/Y') . "<br /><br >
            Cordialement,<br /><br />
            L'équipe IT";

        $message = (new Email())
            ->subject($order->getNumber() . ' livrée')
            ->from(new Address('dev@tediber.com', 'TEDIBER'))
            ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::INFLUENCER_ORDER)->getPrincipals())
            ->html($body);

        $this->mailer->send($message);
    }

    private function checkCode(Order $order): bool
    {
        if (null !== $order->getPromotionCoupon() && null !== $order->getPromotionCoupon()->getPromotion()) {
            $code = strtolower((string) $order->getPromotionCoupon()->getPromotion()->getCode());
            if (str_contains($code, self::PROMOTION_INFLUENCER_KEYWORD)) {
                return true;
            }
        }

        return false;
    }
}
