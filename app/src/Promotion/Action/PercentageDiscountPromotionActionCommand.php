<?php

declare(strict_types=1);

namespace App\Promotion\Action;

use App\Doctrine\Entity\Sylius\Order;
use Sylius\Component\Core\Distributor\ProportionalIntegerDistributorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Promotion\Action\DiscountPromotionActionCommand;
use Sylius\Component\Core\Promotion\Applicator\UnitsPromotionAdjustmentsApplicatorInterface;
use Sylius\Component\Promotion\Action\PromotionActionCommandInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Webmozart\Assert\Assert;

final class PercentageDiscountPromotionActionCommand extends DiscountPromotionActionCommand implements PromotionActionCommandInterface
{
    public const TYPE = 'order_percentage_discount';

    public function __construct(
        private readonly ProportionalIntegerDistributorInterface $distributor,
        private readonly UnitsPromotionAdjustmentsApplicatorInterface $unitsPromotionAdjustmentsApplicator
    ) {
    }

    public function execute(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion): bool
    {
        /* @var Order $subject */
        Assert::isInstanceOf($subject, OrderInterface::class);

        if (!$this->isSubjectValid($subject)) {
            return false;
        }

        try {
            $this->isConfigurationValid($configuration);
        } catch (\InvalidArgumentException) {
            return false;
        }

        $promotionAmount = $this->calculateAdjustmentAmount($subject->getPromotionSubjectTotal(), $configuration['percentage']);
        if (0 === $promotionAmount) {
            return false;
        }

        $itemsTotal = [];
        foreach ($subject->getPromotionableItems() as $orderItem) {
            $itemsTotal[] = $orderItem->getTotal();
        }

        $splitPromotion = $this->distributor->distribute($itemsTotal, $promotionAmount);
        $this->unitsPromotionAdjustmentsApplicator->apply($subject, $promotion, $splitPromotion);

        return true;
    }

    protected function isConfigurationValid(array $configuration): void
    {
        Assert::keyExists($configuration, 'percentage');
        Assert::greaterThan($configuration['percentage'], 0);
        Assert::lessThanEq($configuration['percentage'], 1);
    }

    private function calculateAdjustmentAmount(int $promotionSubjectTotal, float $percentage): int
    {
        return -1 * (int) round($promotionSubjectTotal * $percentage);
    }
}
