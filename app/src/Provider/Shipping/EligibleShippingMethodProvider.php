<?php

declare(strict_types=1);

namespace App\Provider\Shipping;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Model\ProductReferenceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Shipping\Calculator\DelegatingCalculatorInterface;
use Sylius\Component\Shipping\Checker\Eligibility\ShippingMethodEligibilityCheckerInterface;

class EligibleShippingMethodProvider
{
    public function __construct(
        private readonly ShippingMethodEligibilityCheckerInterface $eligibilityChecker,
        private readonly DelegatingCalculatorInterface $shippingChargesCalculator,
    ) {
    }

    /**
     * @param iterable<Shipment> $shipments
     *
     * @return Collection<array-key, ShippingMethod>
     */
    public function provideEligibleShippingMethods(ShippingMethodCategory $shippingMethodCategory, iterable $shipments, Channel $channel): Collection
    {
        $lowestPrice = null;
        $eligibleShippingMethods = new ArrayCollection();
        foreach ($shippingMethodCategory->getShippingMethods() as $shippingMethod) {
            if (!$shippingMethod->hasChannel($channel)) {
                continue;
            }

            foreach ($shipments as $shipment) {
                $clonedShipment = clone $shipment;

                /** @var OrderItemUnit $clonedUnit */
                foreach ($clonedShipment->getUnits() as $clonedUnit) {
                    /** @var ProductVariant $shippableUnit */
                    $shippableUnit = $clonedUnit->getShippable();

                    // We exclude Assembly And Retrieval items
                    if (in_array($shippableUnit->getCode(), ProductReferenceInterface::REPOSSESS_REFS, true)
                        || in_array($shippableUnit->getCode(), ProductReferenceInterface::ASSEMBLY_CODES, true)) {
                        $clonedShipment->removeUnit($clonedUnit);
                    }
                }

                // The LowerOrEqualThanTheMaximumAllowedMaxArticlesRuleChecker needs a Shipment with a method
                $clonedShipment->setMethod($shippingMethod);
                if (!$this->eligibilityChecker->isEligible($clonedShipment, $shippingMethod)) {
                    continue;
                }

                $eligibleShippingMethods->add($shippingMethod);

                $price = $this->shippingChargesCalculator->calculate($clonedShipment);
                if (null === $lowestPrice || $price < $lowestPrice) {
                    $lowestPrice = $price;
                }
            }
        }

        $shippingMethodCategory->setLowestPrice($lowestPrice);

        return $eligibleShippingMethods;
    }
}
