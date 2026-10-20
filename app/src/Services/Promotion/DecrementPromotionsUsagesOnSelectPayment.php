<?php

namespace App\Services\Promotion;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\Promotion\Modifier\OrderPromotionsUsageModifierInterface;

final class DecrementPromotionsUsagesOnSelectPayment
{
    public function __construct(
        private readonly OrderPromotionsUsageModifierInterface $orderPromotionsUsageModifier
    ) {
    }

    public function __invoke(OrderInterface $order): OrderInterface
    {
        if (OrderCheckoutStates::STATE_COMPLETED === $order->getCheckoutState()) {
            $this->orderPromotionsUsageModifier->decrement($order);
        }

        return $order;
    }
}
