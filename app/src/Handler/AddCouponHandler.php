<?php

namespace App\Handler;

use App\Business\Command\AddCoupon;
use App\Checker\PromotionCouponEligibilityChecker;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Doctrine\Repository\Sylius\PromotionCouponRepository;
use App\Exception\InvalidCouponOperation;
use Setono\SyliusGiftCardPlugin\Applicator\GiftCardApplicator;
use Setono\SyliusGiftCardPlugin\Controller\Action\AddGiftCardToOrderCommand;
use Setono\SyliusGiftCardPlugin\Repository\GiftCardRepositoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Webmozart\Assert\Assert;

final class AddCouponHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly PromotionCouponRepository $couponRepository,
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly PromotionCouponEligibilityChecker $couponEligibilityChecker,
        private readonly GiftCardRepositoryInterface $giftCardRepository,
        private readonly GiftCardApplicator $giftCardApplicator
    ) {
    }

    public function handle(AddCoupon $addCoupon): void
    {
        /** @var OrderInterface $cart */
        $cart = $this->orderRepository->getOneByTokenValueHydrated($addCoupon->orderToken());
        Assert::notNull($cart, sprintf('Cart with token %s has not been found.', $addCoupon->orderToken()));

        // avoid adding promotion coupon while waiting for payment
        if (OrderCheckoutStates::STATE_COMPLETED === $cart->getCheckoutState()) {
            throw new InvalidCouponOperation('Le coupon ne peut être ajouté à votre panier. Merci de supprimer les articles de votre panier et recommencer votre commande');
        }

        /** @var PromotionCouponInterface|null $coupon */
        $coupon = $this->couponRepository->findOneBy(['code' => $addCoupon->couponCode()]);
        $giftCard = null;
        if (null === $coupon) {
            $giftCard = $this->giftCardRepository->findOneBy(['code' => $addCoupon->couponCode()]);
            if (null === $giftCard) {
                throw new InvalidCouponOperation(sprintf("Le code promo %s n'existe pas.", $addCoupon->couponCode()));
            }

            $addGiftCardToOrderCommand = new AddGiftCardToOrderCommand();
            $addGiftCardToOrderCommand->setGiftCard($giftCard);
            $this->giftCardApplicator->apply($cart, $giftCard);
        }

        if (null === $giftCard) {
            if (false === $this->couponEligibilityChecker->isEligible($cart, $coupon)) {
                throw new InvalidCouponOperation(sprintf('Le code promo %s est invalide.', $addCoupon->couponCode()));
            }

            $cart->setPromotionCoupon($coupon);

            $this->orderProcessor->process($cart);
        }
    }
}
