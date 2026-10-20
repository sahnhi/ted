<?php

declare(strict_types=1);

namespace App\Promotion\Checker\Eligibility;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Promotion;
use App\Exception\UnsupportedPromotionActionException;
use App\Specification\Promotion\PromotionIsAdvantageousSpecification;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionCouponEligibilityCheckerInterface;
use Sylius\Component\Promotion\Model\PromotionCouponInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

class PromotionCouponIsAdvantageousEligibilityChecker implements PromotionCouponEligibilityCheckerInterface
{
    public function __construct(
        private readonly PromotionIsAdvantageousSpecification $promotionIsAdvantageousSpecification,
    ) {
    }

    public function isEligible(PromotionSubjectInterface $promotionSubject, PromotionCouponInterface $promotionCoupon): bool
    {
        if (!$promotionSubject instanceof Order) {
            return true; // Or false ?
        }

        if ($promotionSubject->getItems()->isEmpty()) {
            return true;
        }

        /** @var Promotion $promotion */
        $promotion = $promotionCoupon->getPromotion();

        foreach ($promotionSubject->getItems() as $item) {
            try {
                if (($this->promotionIsAdvantageousSpecification)($promotionSubject, $item, $promotion)) {
                    return true;
                }
            } catch (UnsupportedPromotionActionException) {
                return true;
            }
        }

        return false;
    }
}
