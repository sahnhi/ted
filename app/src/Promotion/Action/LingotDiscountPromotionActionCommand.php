<?php

namespace App\Promotion\Action;

use App\Doctrine\Entity\Sylius\ProductInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Promotion\Action\UnitDiscountPromotionActionCommand;
use Sylius\Component\Core\Promotion\Filter\FilterInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Sylius\Component\Resource\Factory\FactoryInterface;

class LingotDiscountPromotionActionCommand extends UnitDiscountPromotionActionCommand
{
    public const TYPE = 'lingot_discount';

    public const MATTRESS_PRODUCTS = [ProductInterface::PRODUCT_MATTRESS, ProductInterface::PRODUCT_PELOTE_MATTRESS];
    public const FRAME_PRODUCTS = [ProductInterface::PRODUCT_BED_FRAME, ProductInterface::PRODUCT_KID_STORAGE_BED_FRAME, ProductInterface::PRODUCT_STORAGE_BED_FRAME];

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

        $filteredItems = $this->priceRangeFilter->filter(
            $subject->getItems()->toArray(),
            array_merge(['channel' => $subject->getChannel()], $configuration[$channelCode])
        );
        $filteredTaxons = $this->taxonFilter->filter($filteredItems, $configuration[$channelCode]);
        $filteredProducts = $this->productFilter->filter($filteredItems, $configuration[$channelCode]);

        if (empty($filteredProducts)) {
            return false;
        }

        if ($configuration['lingot']) {
            $mattress = 0;
            $bedframe = 0;
            $otherFromTaxon = 0;
            $giftGiven = 0;

            foreach ($filteredTaxons as $item) {
                if ($configuration['maxGift'] > 0) {
                    if ($giftGiven >= $configuration['maxGift']) {
                        break;
                    }

                    ++$giftGiven;
                }

                foreach ($item->getUnits() as $unit) {
                    if (in_array($item->getVariant()->getProduct()->getCode(), self::MATTRESS_PRODUCTS)) {
                        ++$mattress;
                    } elseif (in_array($item->getVariant()->getProduct()->getCode(), self::FRAME_PRODUCTS)) {
                        ++$bedframe;
                    } else {
                        ++$otherFromTaxon;
                    }
                }
            }

            $combo = 0;
            $productGifted = 0;

            if ($mattress > 0 || $bedframe > 0) {
                if ($bedframe > 0) {
                    $combo = $mattress + $bedframe - ($bedframe > $mattress ? $bedframe : $mattress);
                }

                $productGifted = ($combo > 0 ? $combo + $mattress : $mattress);
            } elseif ($otherFromTaxon > 0) {
                $productGifted = $otherFromTaxon;
            }

            foreach ($filteredProducts as $item) {
                foreach ($item->getUnits() as $unit) {
                    if ($productGifted > 0) {
                        $this->addAdjustmentToUnit(
                            $unit,
                            $unit->getTotal(),
                            $promotion
                        );

                        --$productGifted;
                    }
                }
            }

            return true;
        } elseif (0 < $configuration['maxGift']) {
            $giftGiven = 1;
            foreach ($filteredProducts as $item) {
                foreach ($item->getUnits() as $unit) {
                    if ($giftGiven <= $configuration['maxGift']) {
                        $this->addAdjustmentToUnit(
                            $unit,
                            $unit->getTotal(),
                            $promotion
                        );

                        ++$giftGiven;
                    }
                }
            }

            return true;
        }

        return false;
    }
}
