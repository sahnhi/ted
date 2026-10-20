<?php

namespace App\Promotion\Checker;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

class VariantRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'variant';

    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnsupportedTypeException($subject, OrderInterface::class);
        }

        foreach ($subject->getItems() as $item) {
            if ($configuration['product_variant_code'] === $item->getVariant()->getCode()) {
                return true;
            }
        }

        return false;
    }
}
