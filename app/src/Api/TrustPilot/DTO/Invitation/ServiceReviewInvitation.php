<?php

namespace App\Api\TrustPilot\DTO\Invitation;

use App\Doctrine\Entity\Sylius\Order;

class ServiceReviewInvitation
{
    public const REDIRECT_URI = 'https://www.tediber.com/remerciement-avis?token=';
    /** @var \DateTime */
    private $preferredSendTime;
    /** @var string */
    private $redirectUri;
    /** @var array */
    private $tags;

    public function __construct(
        Order $order,
        private readonly string $templateId
    ) {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT1H'));
        $this->preferredSendTime = $date;

        $this->redirectUri = self::REDIRECT_URI
            . $order->getTokenValue()
            . '&email='
            . $order->getCustomer()->getEmail()
            . '&rating=true&uid='
            . $order->getCustomer()->getId();

        $this->tags = [
            'Sylius',
            $order->getChannel()->getName(),
        ];
    }
}
