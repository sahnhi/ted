<?php

namespace App\Api\VerifiedReviews;

use App\Api\VerifiedReviews\Invitation\Message;
use App\Doctrine\Entity\Sylius\Order;

class Invitation
{
    private readonly Message $message;

    public function __construct(
        Order $order,
        private readonly string $idWebsite,
        string $apiSecret
    ) {
        $this->message = new Message($order);
        $this->message->sign($apiSecret);
    }
}
