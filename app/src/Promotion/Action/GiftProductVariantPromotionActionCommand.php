<?php

declare(strict_types=1);

namespace App\Promotion\Action;

use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Promotion\Action\PromotionActionCommandInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Sylius\Component\Resource\Factory\FactoryInterface;

class GiftProductVariantPromotionActionCommand implements PromotionActionCommandInterface
{
    public const TYPE = 'gift_product_variant';

    public const ADJUSTMENT_TYPE = 'gifted_product_variant';

    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly FactoryInterface $orderItemFactory,
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
    ) {
    }

    public function execute(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): bool
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnexpectedTypeException($subject, OrderInterface::class);
        }

        if (!isset($configuration['variants'])) {
            throw new \InvalidArgumentException('Configuration must contain "variants" key.');
        }

        $adjustmentType = $this->buildAdjustmentType($promotion);

        foreach ($configuration['variants'] as $variantCode) {
            /** @var ProductVariant|null $variant */
            $variant = $this->productVariantRepository->findOneBy(['code' => $variantCode, 'enabled' => true]);
            if (null === $variant) {
                continue;
            }

            $channelPricing = $variant->getChannelPricingForChannel($subject->getChannel());
            $unitPrice = $channelPricing?->getPrice() ?? 0;

            /** @var OrderItem $orderItem */
            $orderItem = $this->orderItemFactory->createNew();
            $orderItem->setVariant($variant);
            $orderItem->setUnitPrice($unitPrice);

            $this->orderItemQuantityModifier->modify($orderItem, 1);

            // We add an adjustment to make this orderItem free
            $orderItem->addAdjustment(
                $this->adjustmentFactory->createWithData(
                    type: AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT,
                    label: $promotion->getName(),
                    amount: -$unitPrice
                )
            );

            // We add an adjustment to flag the orderItem
            $orderItem->addAdjustment(
                $this->adjustmentFactory->createWithData(
                    type: $adjustmentType,
                    label: $promotion->getName(),
                    amount: 0,
                    neutral: true
                )
            );

            // We DO NOT want to use the OrderModifier service, since it wall call the OrderProcessor and cause
            // unpredictable results (such as infinite loop)
            $subject->addItem($orderItem);
        }

        return true;
    }

    public function revert(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): void
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnexpectedTypeException($subject, OrderInterface::class);
        }

        if (!isset($configuration['variants'])) {
            throw new \InvalidArgumentException('Configuration must contain "variants" key.');
        }

        $adjustmentType = $this->buildAdjustmentType($promotion);

        foreach ($subject->getItems() as $item) {
            if (in_array($item->getVariant()?->getCode(), $configuration['variants'], true) && !$item->getAdjustments($adjustmentType)->isEmpty()) {
                // We DO NOT want to use the OrderModifier service, since it wall call the OrderProcessor and cause
                // unpredictable results (such as infinite loop)
                $subject->removeItem($item);
            }
        }
    }

    private function buildAdjustmentType(PromotionInterface $promotion): string
    {
        return sprintf('%s_%s', self::ADJUSTMENT_TYPE, $promotion->getCode());
    }
}
