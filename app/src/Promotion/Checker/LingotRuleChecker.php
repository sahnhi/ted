<?php

namespace App\Promotion\Checker;

use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

class LingotRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'lingot';

    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        return true;
    }
}
