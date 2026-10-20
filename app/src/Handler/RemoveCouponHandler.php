<?php

namespace App\Handler;

use App\Business\Command\RemoveCoupon;
use App\Exception\InvalidCouponOperation;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Webmozart\Assert\Assert;

final class RemoveCouponHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderProcessorInterface $orderProcessor
    ) {
    }

    public function handle(RemoveCoupon $removeCoupon)
    {
        /** @var OrderInterface $cart */
        $cart = $this->orderRepository->getOneByTokenValueHydrated($removeCoupon->orderToken());
        Assert::notNull($cart, sprintf('Cart with token %s has not been found.', $removeCoupon->orderToken()));

        if (OrderCheckoutStates::STATE_COMPLETED === $cart->getCheckoutState()) {
            throw new InvalidCouponOperation('Le coupon ne peut être retiré de votre panier. Merci de supprimer les articles de votre panier et recommencer votre commande');
        }

        $cart->setPromotionCoupon(null);
        $this->orderProcessor->process($cart);
    }
}
