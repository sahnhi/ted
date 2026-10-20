<?php

namespace App\Promotion\Action;

use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Promotion\Action\UnitDiscountPromotionActionCommand;
use Sylius\Component\Core\Promotion\Filter\FilterInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Sylius\Component\Resource\Factory\FactoryInterface;

class GiftProductDiscountPromotionActionCommand extends UnitDiscountPromotionActionCommand
{
    public const TYPE = 'gift_product_discount';

    public function __construct(
        FactoryInterface $adjustmentFactory,
        private readonly FilterInterface $priceRangeFilter,
        private readonly FilterInterface $taxonFilter,
        private readonly FilterInterface $productFilter
    ) {
        parent::__construct($adjustmentFactory);
    }

    public function execute(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): bool
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnexpectedTypeException($subject, OrderInterface::class);
        }

        $channelCode = $subject->getChannel()->getCode();
        if (!isset($configuration[$channelCode])) {
            return false;
        }

        /** {@inheritdoc} Custom from original file */
        $maxGifted = $configuration['maxGifted'];
        if (0 === $maxGifted) {
            return false;
        }

        $filteredItems = $this->priceRangeFilter->filter(
            $subject->getItems()->toArray(),
            array_merge(['channel' => $subject->getChannel()], $configuration[$channelCode])
        );
        $cartMinTotal = $configuration['cartMinTotal'] ?? null;
        if (!empty($cartMinTotal)) {
            $cartMinTotal = $cartMinTotal * 100;
        }

        $filteredItems = $this->taxonFilter->filter($filteredItems, $configuration[$channelCode]);
        $filteredItems = $this->productFilter->filter($filteredItems, $configuration[$channelCode]);

        if (empty($filteredItems)) {
            return false;
        }

        $giftGiven = 1;
        if (null === $cartMinTotal || (!empty($cartMinTotal) && $subject->getTotal() >= $cartMinTotal)) {
            foreach ($filteredItems as $item) {
                for ($i = 1; $i <= $item->getQuantity(); ++$i) {
                    if ($giftGiven <= $maxGifted) {
                        $adjustment = $this->adjustmentFactory->createNew();
                        $adjustment->setType(AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT);
                        $adjustment->setAmount(-$item->getUnitPrice());
                        $adjustment->setNeutral(false);
                        $adjustment->setLabel('Gift product');

                        $item->addAdjustment($adjustment, $promotion);

                        ++$giftGiven;
                    }
                }
            }
        }

        return true;
    }
}
