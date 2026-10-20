<?php

namespace App\Modifier;

use Doctrine\Persistence\ObjectManager;
use Setono\SyliusGiftCardPlugin\Model\AdjustmentInterface;
use Setono\SyliusGiftCardPlugin\Model\GiftCardInterface;
use Setono\SyliusGiftCardPlugin\Model\OrderInterface;
use Setono\SyliusGiftCardPlugin\Modifier\OrderGiftCardAmountModifierInterface;

/**
 * This class' responsibility is to modify the amount on a gift card when it's used to pay for an order.
 */
final class OrderGiftCardAmountModifier implements OrderGiftCardAmountModifierInterface
{
    private OrderInterface $order;

    public function getOrder(): OrderInterface
    {
        return $this->order;
    }

    public function __construct(
        private readonly ObjectManager $giftCardManager
    ) {
    }

    public function decrement(OrderInterface $order): void
    {
        foreach ($order->getAdjustments(AdjustmentInterface::ORDER_GIFT_CARD_ADJUSTMENT) as $adjustment) {
            $giftCard = $this->getGiftCard($order, (string) $adjustment->getOriginCode());

            $amount = abs($adjustment->getAmount());

            if ($amount >= $giftCard->getAmount()) {
                $giftCard->disable();
                $giftCard->setAmount(0);
            }

            if ($amount < $giftCard->getAmount()) {
                $giftCard->enable();

                $giftCard->setAmount($giftCard->getAmount() - $amount);
            }
        }

        $this->giftCardManager->flush();

        $this->order = $order;
    }

    public function increment(OrderInterface $order): void
    {
        foreach ($order->getAdjustments(AdjustmentInterface::ORDER_GIFT_CARD_ADJUSTMENT) as $adjustment) {
            $giftCard = $this->getGiftCard($order, (string) $adjustment->getOriginCode());

            $giftCard->setAmount($giftCard->getAmount() + abs($adjustment->getAmount()));

            if ($giftCard->getAmount() > 0) {
                $giftCard->enable();
            }
        }

        $this->giftCardManager->flush();

        $this->order = $order;
    }

    public function resetIfDisable(OrderInterface $order): void
    {
        foreach ($order->getAdjustments(AdjustmentInterface::ORDER_GIFT_CARD_ADJUSTMENT) as $adjustment) {
            $giftCard = $this->getGiftCard($order, (string) $adjustment->getOriginCode());

            if (!$giftCard->isEnabled()) {
                $reset = $giftCard->getInitialAmount() - $giftCard->getAmount();
                $giftCard->setAmount($giftCard->getAmount() + abs($reset));

                if ($giftCard->getAmount() > 0) {
                    $giftCard->enable();
                }
            }
        }

        $this->giftCardManager->flush();

        $this->order = $order;
    }

    private function getGiftCard(OrderInterface $order, string $code): GiftCardInterface
    {
        foreach ($order->getGiftCards() as $giftCard) {
            if ($giftCard->getCode() === $code) {
                return $giftCard;
            }
        }

        throw new \RuntimeException(sprintf('The order %s does not have a gift card with code %s', $order->getNumber(), $code));
    }
}
