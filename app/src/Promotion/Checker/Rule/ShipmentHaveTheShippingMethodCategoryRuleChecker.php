<?php

declare(strict_types=1);

namespace App\Promotion\Checker\Rule;

use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

class ShipmentHaveTheShippingMethodCategoryRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'shipment_has_the_shipping_method_category';

    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!isset($configuration['shippingMethodCategory'])) {
            return false;
        }

        if (!$subject instanceof OrderInterface) {
            throw new UnsupportedTypeException($subject, OrderInterface::class);
        }

        /** @var Shipment $shipment */
        foreach ($subject->getShipments() as $shipment) {
            if ($shipment->getShippingMethodCategory()?->getCode() === $configuration['shippingMethodCategory']) {
                return true;
            }

            /** @var ShippingMethod $method */
            $method = $shipment->getMethod();
            if ($method->getShippingMethodCategory()?->getCode() === $configuration['shippingMethodCategory']) {
                return true;
            }
        }

        return false;
    }
}
