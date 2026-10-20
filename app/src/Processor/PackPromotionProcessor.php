<?php

declare(strict_types=1);

namespace App\Processor;

use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Promotion\Action\UnitPercentageDiscountPromotionExclusionActionCommand;
use Sylius\Component\Core\Promotion\Action\UnitPercentageDiscountPromotionActionCommand;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Promotion\Action\PromotionApplicatorInterface;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionEligibilityCheckerInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Provider\PreQualifiedPromotionsProviderInterface;

final class PackPromotionProcessor implements OrderProcessorInterface
{
    public function __construct(
        private readonly PreQualifiedPromotionsProviderInterface $preQualifiedPromotionsProvider,
        private readonly PromotionEligibilityCheckerInterface $promotionEligibilityChecker,
        private readonly PromotionApplicatorInterface $promotionApplicator
    ) {
    }

    public function process(BaseOrderInterface $order): void
    {
        if (!$order instanceof OrderInterface
            || null === $order->getPromotionCoupon()
            || null === ($channelCode = $order->getChannel()?->getCode())
        ) {
            return;
        }

        $packItems = [];
        $items = $order->getItems();
        foreach ($items as $item) {
            /** @var ProductVariantInterface $variant */
            $variant = $item->getVariant();
            if ($variant->isBundle()) {
                $packItems[] = $item;
            }
        }

        if (empty($packItems)) {
            return;
        }

        $promotions = $this->preQualifiedPromotionsProvider->getPromotions($order);

        foreach ($promotions as $promotion) {
            if (!$this->promotionEligibilityChecker->isEligible($order, $promotion)) {
                continue;
            }

            foreach ($packItems as $packItem) {
                foreach ($promotion->getActions() as $promotionAction) {
                    $configuration = $promotionAction->getConfiguration();

                    switch ($promotionAction->getType()) {
                        case UnitPercentageDiscountPromotionExclusionActionCommand::TYPE:
                            $percentage = $configuration['percentage'];
                            $productFilters = $configuration['filters']['products_filter']['products'];
                            break;
                        case UnitPercentageDiscountPromotionActionCommand::TYPE:
                            $percentage = $configuration[$channelCode]['percentage'];
                            $productFilters = $configuration[$channelCode]['filters']['products_filter']['products'];
                            break;
                        default:
                            $percentage = null;
                            $productFilters = [];
                    }

                    if (null === $percentage) {
                        continue;
                    }

                    if (in_array($packItem->getProduct()->getCode(), $productFilters ?? [], true)) {
                        $this->computeBestPackPromotion($promotion, $packItem, (float) $percentage, $order);
                    }
                }
            }
        }
    }

    private function computeBestPackPromotion(PromotionInterface $promotion, OrderItemInterface $orderItem, float $percentage, OrderInterface $order): void
    {
        // compute the best promotion for the pack
        $filteredUnits = $orderItem->getUnits()->filter(function (OrderItemUnit $unit) use ($orderItem, $percentage): bool {
            if ($unit->getAdjustments()->isEmpty()) {
                return false;
            }

            $currentAdjustmentAmount = abs($unit->getAdjustments()->first()->getAmount() / $orderItem->getUnitPrice());

            return $currentAdjustmentAmount < $percentage;
        });

        if (false === $filteredUnits->isEmpty()) {
            // We remove the adjustments on those filtered units since we know a more advantageous promotion will be applied
            $filteredUnits->map(function (OrderItemUnit $unit): void {
                $unit->removeAdjustments();
            });

            $this->promotionApplicator->apply($order, $promotion);
        }
    }
}
