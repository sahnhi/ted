<?php

declare(strict_types=1);

namespace App\Promotion\Action;

use App\Doctrine\Entity\Sylius\OrderInterface;
use Sylius\Component\Core\Distributor\ProportionalIntegerDistributorInterface;
use Sylius\Component\Core\Promotion\Action\UnitDiscountPromotionActionCommand;
use Sylius\Component\Core\Promotion\Applicator\UnitsPromotionAdjustmentsApplicatorInterface;
use Sylius\Component\Core\Promotion\Filter\FilterInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class UnitPercentageDiscountPromotionExclusionActionCommand extends UnitDiscountPromotionActionCommand
{
    public const TYPE = 'unit_percentage_discount_with_exclusion';

    public function __construct(
        FactoryInterface $adjustmentFactory,
        private readonly FilterInterface $taxonFilter,
        private readonly FilterInterface $productFilter,
        private readonly ProportionalIntegerDistributorInterface $distributor,
        private readonly UnitsPromotionAdjustmentsApplicatorInterface $unitsPromotionAdjustmentsApplicator
    ) {
        parent::__construct($adjustmentFactory);
    }

    public function execute(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): bool
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnexpectedTypeException($subject, OrderInterface::class);
        }

        $filteredItems = $this->taxonFilter->filter($subject->getPromotionableItems()->toArray(), $configuration);
        $filteredItems = $this->productFilter->filter($filteredItems, $configuration);

        if (empty($filteredItems)) {
            return false;
        }

        $filteredTotal = 0;
        foreach ($filteredItems as $item) {
            $filteredTotal += $item->getTotal();
        }

        $promotionAmount = $this->calculateAdjustmentAmount($filteredTotal, $configuration['percentage']);
        if (0 === $promotionAmount) {
            return false;
        }

        $itemsTotal = [];
        foreach ($filteredItems as $orderItem) {
            $itemsTotal[] = $orderItem->getTotal();
        }

        $splitPromotion = $this->distributor->distribute($itemsTotal, $promotionAmount);
        $this->unitsPromotionAdjustmentsApplicator->apply($subject, $promotion, $splitPromotion);

        return true;
    }

    private function calculateAdjustmentAmount(int $promotionSubjectTotal, float $percentage): int
    {
        return -1 * (int) round($promotionSubjectTotal * $percentage);
    }
}
