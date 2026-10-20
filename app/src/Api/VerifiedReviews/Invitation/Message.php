<?php

namespace App\Api\VerifiedReviews\Invitation;

use App\Doctrine\Entity\Sylius\Order;
use JMS\Serializer\Annotation\SerializedName;

class Message
{
    private string $query = 'pushCommandeSHA1';
    /**
     * @SerializedName("order_ref")
     */
    private readonly string $orderRef;
    private readonly string $firstname;
    private readonly string $lastname;
    private readonly string $email;
    /**
     * @SerializedName("order_date")
     */
    private readonly string $orderDate;
    private string $sign;

    public function __construct(Order $order)
    {
        $this->orderRef = $order->getNumber();
        $this->firstname = $order->getCustomer()->getFirstName();
        $this->lastname = $order->getCustomer()->getLastName();
        $this->email = $order->getCustomer()->getEmail();
        $this->orderDate = $order->getCheckoutCompletedAt()->format('Y-m-d H:i:s');
    }

    public function sign(string $apiSecret): void
    {
        $sign = $this->query
            . $this->orderRef
            . $this->email
            . $this->lastname
            . $this->firstname
            . $this->orderDate
            . $apiSecret;

        $this->sign = sha1($sign);
    }
}
