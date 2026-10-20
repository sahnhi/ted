<?php

declare(strict_types=1);

namespace App\Resolver\Shipping;

use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\Sylius\ShipmentInterface;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Repository\Sylius\ShippingMethodRepository;
use App\Provider\Shipping\EligibleShippingMethodProvider;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use App\Services\Calendar\ServiceRulesService;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Shipping\Checker\Eligibility\ShippingMethodEligibilityCheckerInterface;
use Sylius\Component\Shipping\Model\ShippingSubjectInterface;
use Sylius\Component\Shipping\Resolver\ShippingMethodsResolverInterface;
use Webmozart\Assert\Assert;

class ShippingMethodCategoryBasedShippingMethodsResolver implements ShippingMethodsResolverInterface
{
    public function __construct(
        private readonly ShippingMethodCategoryProvider $provider,
        private readonly EligibleShippingMethodProvider $eligibleShippingMethodProvider,
        private readonly ZoneMatcherInterface $zoneMatcher,
        private readonly ServiceRulesService $serviceRulesService,
        private readonly ShippingMethodRepository $shippingMethodRepository,
        private readonly ShippingMethodEligibilityCheckerInterface $shippingMethodEligibilityChecker,
    ) {
    }

    public function getSupportedMethods(ShippingSubjectInterface $subject): array
    {
        /* @var ShipmentInterface $subject */
        Assert::isInstanceOf($subject, ShipmentInterface::class);
        Assert::true($this->supports($subject));

        /** @var OrderInterface $order */
        $order = $subject->getOrder();

        if (null !== $subject->getShippingMethodCategory()) {
            return $this->eligibleShippingMethodProvider
                ->provideEligibleShippingMethods($subject->getShippingMethodCategory(), $order->getShipments(), $order->getChannel())
                ->toArray();
        }

        $availableShippingMethods = [];
        foreach ($this->provider->provideEligibleForOrder($order) as $shippingMethodCategory) {
            $availableShippingMethods = [
                ...$availableShippingMethods,
                ...$shippingMethodCategory->getShippingMethods(),
            ];
        }

        // Sylius will look in the shipping method themselves to find the eligible ones.
        // We also need to look in the available service rules since they can give us additional available shipping methods.
        $serviceRulesAvailableShippingMethods = [];
        if (null !== $order->getShippingAddress()) {
            $zoneCodes = [];
            foreach ($this->zoneMatcher->matchAll($order->getShippingAddress(), Scope::SHIPPING) as $zone) {
                $zoneCodes[] = $zone->getCode();
            }

            $serviceRulesZoneAvailable = $this->serviceRulesService->hasServiceZone($zoneCodes, $order->getChannel());

            foreach ($serviceRulesZoneAvailable as $serviceRules) {
                /** @var ShippingMethod|null $serviceRuleShippingMethod */
                $serviceRuleShippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $serviceRules['code']]);
                if (!$serviceRuleShippingMethod) {
                    continue;
                }

                if ([] !== $order->getShippingMethodCategories() && !in_array($serviceRuleShippingMethod->getShippingMethodCategory(), $order->getShippingMethodCategories(), true)) {
                    continue;
                }

                foreach ($order->getShipments() as $shipment) {
                    if (!$this->shippingMethodEligibilityChecker->isEligible($shipment, $serviceRuleShippingMethod)) {
                        continue 2;
                    }
                }

                $serviceRulesAvailableShippingMethods[] = $serviceRuleShippingMethod;
            }
        }

        return [
            ...$availableShippingMethods,
            ...$serviceRulesAvailableShippingMethods,
        ];
    }

    public function supports(ShippingSubjectInterface $subject): bool
    {
        return true;
    }
}
