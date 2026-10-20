<?php

declare(strict_types=1);

namespace App\Checker\Shipping\Rule;

use Sylius\Component\Shipping\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Shipping\Model\ShippingSubjectInterface;

class DoesNotContainsTaxonRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'does_not_contains_taxon';

    public function __construct(
        private readonly RuleCheckerInterface $containsAtLeastTaxonRuleChecker
    ) {
    }

    public function isEligible(ShippingSubjectInterface $subject, array $configuration): bool
    {
        return !$this->containsAtLeastTaxonRuleChecker->isEligible($subject, $configuration);
    }
}
