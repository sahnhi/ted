<?php

namespace App\Services\Calendar;

use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Sylius\Zone;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Doctrine\Repository\Sylius\ShippingMethodRepository;
use App\Doctrine\Repository\ZoneMemberRepository;
use App\Factory\OrderFactoryInterface;
use App\Provider\Shipping\EligibleShippingMethodProvider;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use App\Request\CalendarAvailabilityRequest;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Addressing\Model\ZoneMemberInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Factory\AddressFactoryInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Shipping\Checker\Eligibility\ShippingMethodEligibilityCheckerInterface;

class ZoneService
{
    public const LOGS_ZONE_CP = 0;
    public const LOGS_ZONE_MEMBER = 1;
    public const ZONE_EXCLUDED_POSTFIX = 'ESC';

    private CalendarAvailabilityRequest $request;

    public function getRequest(): CalendarAvailabilityRequest
    {
        return $this->request;
    }

    public function setRequest(CalendarAvailabilityRequest $request): ZoneService
    {
        $this->request = $request;

        return $this;
    }

    public function __construct(
        private readonly LogsCalendarService $logsCalendarService,
        private readonly ZoneMemberRepository $zoneMemberRepository,
        private readonly ShippingMethodRepository $shippingMethodRepository,
        private readonly EligibleShippingMethodProvider $eligibleShippingMethodProvider,
        private readonly ZoneMatcherInterface $zoneMatcher,
        private readonly AddressFactoryInterface $addressFactory,
        private readonly ShippingMethodCategoryProvider $shippingMethodCategoryProvider,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly ServiceRulesService $serviceRulesService,
        private readonly ShippingMethodEligibilityCheckerInterface $shippingMethodEligibilityChecker,
        private readonly OrderFactoryInterface $orderFactory,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function provideFormattedShippingMethods(): ?array
    {
        /** @var Address $address */
        $address = $this->addressFactory->createNew();
        $address->setPostcode($this->request->getPostalCode());
        $address->setCountryCode($this->request->getCountryCode());

        $zones = $this->zoneMatcher->matchAll($address, Scope::SHIPPING);

        if ([] === $zones) {
            $this->logsCalendarService->logsZones($this->request->getPostalCode(), self::LOGS_ZONE_CP);
        }

        $order = $this->request->getOrder();

        /** @var Channel|null $channel */
        $channel = $order?->getChannel() ?? $this->channelRepository->findOneByCode($this->request->getChannelCode());

        if (null !== $order && null !== $channel && [] !== $order->getShippingMethodCategories()) {
            $formattedAvailableShippingMethods = $this->getShippingMethodsFromOrderAndChannel($order, $channel, $zones);
        } else {
            $formattedAvailableShippingMethods = $this->getShippingMethodsFromRequestAndZones($zones);
        }

        $zoneCodes = [];
        foreach ($zones as $zone) {
            $zoneCodes[] = $zone->getCode();
        }

        $serviceRulesZoneAvailable = $this->serviceRulesService->hasServiceZone($zoneCodes, $channel);

        if (null === $order) {
            $order = $this->orderFactory->createNewForChargesEstimate(
                $this->formatProductsFromRequestForChargesEstimates(),
                $channel
            );
        }

        foreach ($serviceRulesZoneAvailable as $key => $serviceRules) {
            /** @var ShippingMethod|null $serviceRuleShippingMethod */
            $serviceRuleShippingMethod = $this->shippingMethodRepository->findOneBy(['code' => $serviceRules['code']]);
            if (!$serviceRuleShippingMethod) {
                continue;
            }

            $orderShippingMethodCategories = $order->getShippingMethodCategories();
            if ([] !== $orderShippingMethodCategories && !in_array($serviceRuleShippingMethod->getShippingMethodCategory(), $orderShippingMethodCategories, true)) {
                unset($serviceRulesZoneAvailable[$key]);
                continue;
            }

            foreach ($order->getShipments() as $shipment) {
                // The LowerOrEqualThanTheMaximumAllowedMaxArticlesRuleChecker needs a Shipment with a method
                $clonedShipment = clone $shipment;
                $clonedShipment->setMethod($serviceRuleShippingMethod);

                if (!$this->shippingMethodEligibilityChecker->isEligible($clonedShipment, $serviceRuleShippingMethod)) {
                    unset($serviceRulesZoneAvailable[$key]);
                    continue 2;
                }
            }
        }

        if (!empty($serviceRulesZoneAvailable)) {
            $formattedAvailableShippingMethods = $this->serviceRulesService->checkShippingPriorityService($formattedAvailableShippingMethods, $serviceRulesZoneAvailable);
        }

        return $formattedAvailableShippingMethods;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getZoneMessages(Zone $zone, array $messages): array
    {
        if (null !== $zone->getMessage()) {
            $messages[] = $zone->getMessage();
        }

        $members = $this->zoneMemberRepository->getZoneMemberByCode($zone->getCode());

        /**
         * @var ZoneMemberInterface $member
         */
        foreach ($members as $member) {
            /** @var Zone $belongsToZone */
            $belongsToZone = $member->getBelongsTo();

            $messages = $this->getZoneMessages($belongsToZone, $messages);
        }

        return $messages;
    }

    /**
     * @throws NonUniqueResultException
     */
    private function getShippingMethodsFromOrderAndChannel(Order $order, Channel $channel, array $zones): array
    {
        $formattedAvailableShippingMethods = [];

        foreach ($order->getShippingMethodCategories() as $shippingMethodCategory) {
            $eligibleShippingMethods = $this->eligibleShippingMethodProvider->provideEligibleShippingMethods(
                $shippingMethodCategory,
                $order->getShipments(),
                $channel,
            );

            foreach ($eligibleShippingMethods as $eligibleShippingMethod) {
                if (in_array($eligibleShippingMethod->getZone(), $zones, true)) {
                    $formattedAvailableShippingMethods[] = $this->shippingMethodRepository->findOneByCode($eligibleShippingMethod->getCode(), $channel->getCode());
                }
            }
        }

        return $formattedAvailableShippingMethods;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getShippingMethodsFromRequestAndZones(array $zones): array
    {
        $variantsWithQuantity = $this->formatProductsFromRequestForChargesEstimates();

        $eligibleShippingMethodCategories = $this->shippingMethodCategoryProvider->provideEligibleForProductVariantsAnZones(
            $variantsWithQuantity,
            $this->channelRepository->findOneByCode($this->request->getChannelCode()),
            $zones,
        );

        $selectedShippingMethodCategoryCode = $this->request->getSelectedShippingMethodCategoryCode();
        if (isset($eligibleShippingMethodCategories[$selectedShippingMethodCategoryCode])) {
            $eligibleShippingMethodCategories = [
                $eligibleShippingMethodCategories[$selectedShippingMethodCategoryCode],
            ];
        }

        $formattedAvailableShippingMethods = [];
        foreach ($eligibleShippingMethodCategories as $shippingMethodCategory) {
            foreach ($shippingMethodCategory->getShippingMethods() as $shippingMethod) {
                if (in_array($shippingMethod->getZone(), $zones, true)) {
                    $formattedAvailableShippingMethods[] = $this->shippingMethodRepository->findOneByCode($shippingMethod->getCode(), $this->request->getChannelCode());
                }
            }
        }

        return $formattedAvailableShippingMethods;
    }

    private function formatProductsFromRequestForChargesEstimates(): array
    {
        $formattedProducts = [];
        foreach ($this->request->getProducts() as $item) {
            $variant = $this->productVariantRepository->findOneByCode($item['code']);
            if (null === $variant) {
                continue;
            }

            $formattedProducts[] = [
                'variant' => $variant,
                'quantity' => $item['quantity'],
            ];
        }

        return $formattedProducts;
    }
}
