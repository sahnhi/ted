<?php

namespace App\Operator\GiftCard;

use App\Model\GiftCardAddress;
use Sylius\Component\Core\Model\OrderInterface;

interface OrderGiftCardOperatorInterface
{
    /**
     * Will create and persist all gift cards on the given order.
     */
    public function create(OrderInterface $order, GiftCardAddress $giftCardAddress): void;

    public function enable(OrderInterface $order): void;

    public function disable(OrderInterface $order): void;
}
