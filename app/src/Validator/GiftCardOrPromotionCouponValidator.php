<?php

declare(strict_types=1);

namespace App\Validator;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use Setono\SyliusGiftCardPlugin\Repository\GiftCardRepositoryInterface;
use Sylius\Bundle\ApiBundle\Command\Cart\ApplyCouponToCart;
use Sylius\Component\Promotion\Repository\PromotionCouponRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class GiftCardOrPromotionCouponValidator extends ConstraintValidator
{
    public function __construct(
        private readonly GiftCardRepositoryInterface $giftCardRepository,
        private readonly ConstraintValidator $promotionCouponValidator,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PromotionCouponRepositoryInterface $promotionCouponRepository
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof ApplyCouponToCart || null === $value->couponCode) {
            return;
        }

        /** @var Order $order */
        $order = $this->orderRepository->findCartByTokenValue($value->getOrderTokenValue());

        $giftCard = $this->giftCardRepository->findOneEnabledByCodeAndChannel($value->couponCode, $order->getChannel());
        // The Setono Gift Card Plugin does not offer a service to check weather or not a gift card is applicable before applying it.
        // Therefore, this validator will assume that the gift card is valid as long as it exists for this channel
        if (null !== $giftCard) {
            return;
        }

        $promotionCoupon = $this->promotionCouponRepository->findOneBy(['code' => $value->couponCode]);
        if (!$promotionCoupon) {
            $this->context->buildViolation('app.coupon.not_found')
                ->addViolation();

            return;
        }

        // If $giftCard is null, we know that it is a coupon. We then let sylius validate the coupon
        if ($order->getPromotionCoupon() === $promotionCoupon) {
            $this->context->buildViolation('app.coupon.already_applied')
                ->addViolation();

            return;
        }

        $this->promotionCouponValidator->validate($value, $constraint);
    }

    public function initialize(ExecutionContextInterface $context): void
    {
        $this->context = $context;
        $this->promotionCouponValidator->initialize($context);
    }
}
