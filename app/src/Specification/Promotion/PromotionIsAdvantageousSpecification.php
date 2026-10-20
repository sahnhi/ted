<?php

declare(strict_types=1);

namespace App\Specification\Promotion;

use App\Doctrine\Entity\Sylius\ChannelPricing;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Exception\UnsupportedPromotionActionException;
use Sylius\Component\Core\Calculator\ProductVariantPricesCalculatorInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Promotion\Action\PromotionApplicatorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class PromotionIsAdvantageousSpecification
{
    public function __construct(
        private readonly ProductVariantPricesCalculatorInterface $productVariantPricesCalculator,
        private readonly PromotionApplicatorInterface $promotionApplicator,
        private readonly FactoryInterface $orderItemFactory,
        private readonly FactoryInterface $orderFactory,
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
    ) {
    }

    /**
     * @throws UnsupportedPromotionActionException
     */
    public function __invoke(Order $order, OrderItem $orderItem, PromotionInterface $promotion): bool
    {
        /** @var Channel $channel */
        $channel = $order->getChannel();

        $catalogPromotionTotalDiscountAmount = $this->calculateItemDiscount($orderItem, $channel);
        if (0 === $catalogPromotionTotalDiscountAmount) {
            return true;
        }

        /** @var Order $fakeOrder */
        $fakeOrder = $this->orderFactory->createNew();
        $fakeOrder->setChannel($channel);

        /** @var OrderItem $fakeOrderItem */
        $fakeOrderItem = $this->orderItemFactory->createNew();
        $fakeOrderItem->setVariant($orderItem->getVariant());

        $fakeOrderItem->setUnitPrice($this->productVariantPricesCalculator->calculateOriginal($orderItem->getVariant(), ['channel' => $channel]));
        $this->orderItemQuantityModifier->modify($fakeOrderItem, $orderItem->getQuantity());

        $fakeOrder->addItem($fakeOrderItem);
        $fakeOrder->recalculateItemsTotal();

        $this->promotionApplicator->apply($fakeOrder, $promotion);

        // We want the adjustments added on our fakeOrderItem, since the promotion might have added other items with inferior discounts
        $addedPromotionAdjustmentTotal = $fakeOrderItem->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT)
            + $fakeOrderItem->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_UNIT_PROMOTION_ADJUSTMENT)
            + $fakeOrderItem->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT);

        if (0 === $addedPromotionAdjustmentTotal && $fakeOrder->getItems()->count() > 1) {
            throw new UnsupportedPromotionActionException('Promotion that adds no discount but adds multiple items cannot be considered advantageous.');
        }

        return abs($addedPromotionAdjustmentTotal) > $catalogPromotionTotalDiscountAmount;
    }

    protected function calculateItemDiscount(OrderItem $orderItem, Channel $channel): int
    {
        /** @var ProductVariantInterface $variant */
        $variant = $orderItem->getVariant();

        /** @var ChannelPricing $channelPricing */
        $channelPricing = $variant->getChannelPricingForChannel($channel);

        return (int) $channelPricing->getDiscountAmount() * $orderItem->getQuantity();
    }
}
