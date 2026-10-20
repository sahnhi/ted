<?php

declare(strict_types=1);

namespace App\Promotion\Checker\Rule;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

class OrderHasCouponRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'order_has_coupon';

    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!isset($configuration['coupon'])) {
            return false;
        }

        if (!$subject instanceof OrderInterface) {
            throw new UnsupportedTypeException($subject, OrderInterface::class);
        }

        return $subject->getPromotionCoupon()?->getCode() === $configuration['coupon'];
    }
}
