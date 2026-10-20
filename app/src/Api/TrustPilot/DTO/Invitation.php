<?php

namespace App\Api\TrustPilot\DTO;

use App\Api\TrustPilot\DTO\Invitation\ServiceReviewInvitation;
use App\Doctrine\Entity\Sylius\Order;

class Invitation
{
    public const REPLY_TO = 'hello@tediber.com';
    //    const SENDER_MAIL = 'hello@tediber.com';
    //    const SENDER_NAME = 'Tediber';
    public const SENDER_MAIL = 'noreply.invitations@trustpilotmail.com';
    public const SENDER_NAME = 'Trust Pilot';

    private readonly string $replyTo;
    private readonly string $locale;
    private readonly string $senderName;
    private readonly string $senderEmail;
    private readonly ?string $locationId;
    private readonly ?string $referenceNumber;
    private readonly string $consumerName;
    private readonly ?string $consumerEmail;
    private readonly ServiceReviewInvitation $serviceReviewInvitation;

    public function __construct(Order $order, string $trustPilotTemplateId)
    {
        $this->replyTo = self::REPLY_TO;
        $this->locale = str_replace('_', '-', (string) $order->getLocaleCode());
        $this->senderName = self::SENDER_NAME;
        $this->senderEmail = self::SENDER_MAIL;
        /* @todo : what does it match */
        $this->locationId = null;
        $this->referenceNumber = $order->getNumber();
        $this->consumerName = $order->getCustomer()->getFullName();
        $this->consumerEmail = $order->getCustomer()->getEmail();
        $this->serviceReviewInvitation = new ServiceReviewInvitation($order, $trustPilotTemplateId);
    }
}
