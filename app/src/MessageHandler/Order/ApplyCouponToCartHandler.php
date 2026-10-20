<?php

declare(strict_types=1);

namespace App\MessageHandler\Order;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use Setono\SyliusGiftCardPlugin\Applicator\GiftCardApplicatorInterface;
use Setono\SyliusGiftCardPlugin\Repository\GiftCardRepositoryInterface;
use Sylius\Bundle\ApiBundle\Command\Cart\ApplyCouponToCart;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ApplyCouponToCartHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly MessageHandlerInterface $decoratedApplyCouponToCartHandler,
        private readonly GiftCardRepositoryInterface $giftCardRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly GiftCardApplicatorInterface $giftCardApplicator,
    ) {
    }

    public function __invoke(ApplyCouponToCart $command): OrderInterface
    {
        /** @var Order|null $cart */
        $cart = $this->orderRepository->findCartByTokenValue($command->getOrderTokenValue());
        if (null === $cart) {
            throw new \InvalidArgumentException('Cart doesn\'t exist');
        }

        $giftCard = !empty($command->couponCode) ? $this->giftCardRepository->findOneEnabledByCodeAndChannel($command->couponCode, $cart->getChannel()) : null;
        if (null !== $giftCard) {
            $this->giftCardApplicator->apply($cart, $giftCard);

            return $cart;
        }

        return ($this->decoratedApplyCouponToCartHandler)($command);
    }
}
