<?php

declare(strict_types=1);

namespace App\Promotion\Applicator;

use App\Doctrine\Entity\Sylius\ChannelPricing;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Promotion\Action\UnitPercentageDiscountPromotionExclusionActionCommand;
use Sylius\Component\Core\Distributor\IntegerDistributorInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Promotion\Applicator\UnitsPromotionAdjustmentsApplicatorInterface;
use Sylius\Component\Core\Promotion\Filter\FilterInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderItemUnitInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Webmozart\Assert\Assert;

final class UnitsPromotionAdjustmentsApplicator implements UnitsPromotionAdjustmentsApplicatorInterface
{
    public function __construct(
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
        private readonly IntegerDistributorInterface $distributor,
        private readonly FilterInterface $taxonFilter,
        private readonly FilterInterface $productFilter,
    ) {
    }

    /**
     * @param Order $order
     *
     * @throws UnsupportedTypeException
     */
    public function apply(OrderInterface $order, PromotionInterface $promotion, array $adjustmentsAmounts): void
    {
        /** @var array<OrderItem> $items */
        $items = $order->getPromotionableItems()->toArray();

        // If there were any filtering done in the promotion command before that altered the amount of orderItems used to populate $adjustmentsAmounts,
        // We need to do it again so we can check whether the count of the filteredItems matches the count of $adjustmentsAmounts or not
        foreach ($promotion->getActions() as $action) {
            if (UnitPercentageDiscountPromotionExclusionActionCommand::TYPE === $action->getType()) {
                $actionConfiguration = $action->getConfiguration();

                $items = $this->taxonFilter->filter($items, $actionConfiguration);
                $items = $this->productFilter->filter($items, $actionConfiguration);
            }
        }

        Assert::eq(count($items), count($adjustmentsAmounts));

        /** @var Channel $channel */
        $channel = $order->getChannel();

        $i = 0;
        foreach ($items as $item) {
            $adjustmentAmount = $adjustmentsAmounts[$i++];
            if (0 === $adjustmentAmount) {
                continue;
            }

            /** @var ChannelPricing $channelPricing */
            $channelPricing = $item->getVariant()->getChannelPricingForChannel($channel);

            $discountAmount = (int) $channelPricing->getDiscountAmount();
            if (0 === $discountAmount || abs($adjustmentAmount) > $discountAmount) {
                $this->applyAdjustmentsOnItemUnits($item, $promotion, $adjustmentAmount);
            }
        }
    }

    private function applyAdjustmentsOnItemUnits(
        OrderItemInterface $item,
        PromotionInterface $promotion,
        int $itemPromotionAmount
    ): void {
        $splitPromotionAmount = $this->distributor->distribute($itemPromotionAmount, $item->getQuantity());

        $i = 0;
        foreach ($item->getUnits() as $unit) {
            $promotionAmount = $splitPromotionAmount[$i++];
            if (0 === $promotionAmount) {
                continue;
            }

            $this->addAdjustment($promotion, $unit, $promotionAmount);
        }
    }

    private function addAdjustment(PromotionInterface $promotion, OrderItemUnitInterface $unit, int $amount): void
    {
        $adjustment = $this->adjustmentFactory
            ->createWithData(AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT, $promotion->getName(), $amount)
        ;
        $adjustment->setOriginCode($promotion->getCode());

        $unit->addAdjustment($adjustment);
    }
}
