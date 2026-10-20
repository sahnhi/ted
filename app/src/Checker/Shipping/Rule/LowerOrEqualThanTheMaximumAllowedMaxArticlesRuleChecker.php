<?php

declare(strict_types=1);

namespace App\Checker\Shipping\Rule;

use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Form\Extension\ShippingMethodRuleTypeExtension;
use Sylius\Component\Core\Repository\ShippingMethodRepositoryInterface;
use Sylius\Component\Shipping\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Shipping\Model\ShippingSubjectInterface;

class LowerOrEqualThanTheMaximumAllowedMaxArticlesRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'lower_or_equal_than_method_max_articles';

    public function __construct(
        private readonly ShippingMethodRepositoryInterface $shippingMethodRepository,
    ) {
    }

    /**
     * @param ShippingSubjectInterface&Shipment $subject
     */
    public function isEligible(ShippingSubjectInterface $subject, array $configuration): bool
    {
        if (!$subject instanceof Shipment) {
            throw new \InvalidArgumentException('The subject must be an instance of Shipment.');
        }

        // Previously, the shipment needed to already have a shippingMethod assigned for the rule to be able to
        // correctly function. Now, the shipping method is saved on the configuration itself, but since not all
        // shipping methods have updated, we need to handle the case where the configuration does NOT contain
        // the needed "shippingMethodCode" configuration.
        if (isset($configuration[ShippingMethodRuleTypeExtension::SHIPPING_METHOD_CODE_INDEX])) {
            /** @var ShippingMethod|null $shippingMethod */
            $shippingMethod = $this->shippingMethodRepository->findOneBy([
                'code' => $configuration[ShippingMethodRuleTypeExtension::SHIPPING_METHOD_CODE_INDEX],
            ]);
        } else {
            /** @var ShippingMethod|null $shippingMethod */
            $shippingMethod = $subject->getMethod();
        }

        $maxAllowedArticlesScore = $shippingMethod?->getMaxArticles();
        if (null === $maxAllowedArticlesScore) {
            return true;
        }

        $shipmentArticlesScore = 0.0;

        /** @var OrderItemUnit $shipmentUnit */
        foreach ($subject->getUnits() as $shipmentUnit) {
            /** @var ProductVariant|null $shippable */
            $shippable = $shipmentUnit->getShippable();

            /** @var Product|null $product */
            $product = $shippable?->getProduct();

            $shipmentArticlesScore += (float) $product?->getWeighting();
        }

        return $shipmentArticlesScore <= $maxAllowedArticlesScore;
    }
}
