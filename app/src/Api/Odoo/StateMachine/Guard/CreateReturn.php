<?php

declare(strict_types=1);

namespace App\Api\Odoo\StateMachine\Guard;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Services\Channel\ChannelCode;

final class CreateReturn
{
    public function __invoke(OrderInterface $order): bool
    {
        if (Reason::RETURN_TYPE_REFUND === $order->getReturnType() && false === $this->orderContainCommercialOffer($order)) {
            return false;
        }

        return ChannelCode::CHANNEL_RETURN === $order->getChannel()->getCode();
    }

    public function orderContainCommercialOffer(BaseOrder $order): bool
    {
        return false === $order->getOrderReason()->filter(
            fn (OrderReason $reason): bool => OrderReason::COMMERCIAL_OFFER_ID === $reason->getReason()->getId()
        )->isEmpty();
    }
}
