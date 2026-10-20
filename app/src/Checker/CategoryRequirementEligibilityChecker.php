<?php

declare(strict_types=1);

namespace App\Checker;

use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use Sylius\Component\Shipping\Checker\Eligibility\ShippingMethodEligibilityCheckerInterface;
use Sylius\Component\Shipping\Model\ShippingMethodInterface as BaseShippingMethodInterface;
use Sylius\Component\Shipping\Model\ShippingSubjectInterface;

class CategoryRequirementEligibilityChecker implements ShippingMethodEligibilityCheckerInterface
{
    /**
     * @param ShippingMethodInterface $shippingMethod
     */
    public function isEligible(ShippingSubjectInterface $shippingSubject, BaseShippingMethodInterface $shippingMethod): bool
    {
        $shippingCategories = $shippingMethod->getShippingCategory();
        if ($shippingCategories->isEmpty()) {
            return true;
        }

        $shippingCategories = $shippingCategories->toArray();

        $numMatches = $numShippables = 0;
        foreach ($shippingSubject->getShippables() as $shippable) {
            ++$numShippables;
            if (in_array($shippable->getShippingCategory(), $shippingCategories, true)) {
                ++$numMatches;
            }
        }

        return match ($shippingMethod->getCategoryRequirement()) {
            BaseShippingMethodInterface::CATEGORY_REQUIREMENT_MATCH_NONE => 0 === $numMatches,
            BaseShippingMethodInterface::CATEGORY_REQUIREMENT_MATCH_ANY => 0 < $numMatches,
            BaseShippingMethodInterface::CATEGORY_REQUIREMENT_MATCH_ALL => $numShippables === $numMatches,
            default => false,
        };
    }
}
