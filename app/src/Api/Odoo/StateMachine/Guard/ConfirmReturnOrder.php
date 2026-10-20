<?php

declare(strict_types=1);

namespace App\Api\Odoo\StateMachine\Guard;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Services\Channel\ChannelCode;
use Sylius\Component\Core\OrderPaymentStates;

final class ConfirmReturnOrder
{
    public function __invoke(Order $order): bool
    {
        $orderChannelCode = $order->getChannel()->getCode();

        if (ChannelCode::CHANNEL_RETURN !== $orderChannelCode) {
            return false;
        }

        if (OrderPaymentStates::STATE_REFUNDED !== $order->getPaymentState()) {
            return false;
        }

        if (Reason::RETURN_TYPE_REFUND === $order->getReturnType() && !$this->orderContainCommercialOffer($order)) {
            return false;
        }

        return true;
    }

    public function orderContainCommercialOffer(BaseOrder $order): bool
    {
        return false === $order->getOrderReason()->filter(
            fn (OrderReason $reason): bool => OrderReason::COMMERCIAL_OFFER_ID === $reason->getReason()->getId()
        )->isEmpty();
    }
}
