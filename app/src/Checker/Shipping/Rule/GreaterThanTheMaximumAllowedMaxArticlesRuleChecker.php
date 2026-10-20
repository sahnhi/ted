<?php

declare(strict_types=1);

namespace App\Checker\Shipping\Rule;

use Sylius\Component\Shipping\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Shipping\Model\ShippingSubjectInterface;

class GreaterThanTheMaximumAllowedMaxArticlesRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'greater_than_method_max_articles';

    public function __construct(
        private readonly RuleCheckerInterface $lowerOrEqualThanTheMaximumAllowedMaxArticlesRuleChecker
    ) {
    }

    public function isEligible(ShippingSubjectInterface $subject, array $configuration): bool
    {
        return !$this->lowerOrEqualThanTheMaximumAllowedMaxArticlesRuleChecker->isEligible($subject, $configuration);
    }
}
