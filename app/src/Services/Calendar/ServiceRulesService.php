<?php

namespace App\Services\Calendar;

use App\Doctrine\Entity\Sylius\ShippingCategory;
use App\Doctrine\Repository\ServiceRulesRepository;
use Sylius\Component\Core\Model\ChannelInterface;

class ServiceRulesService
{
    public function __construct(
        private readonly ServiceRulesRepository $serviceRulesRepository
    ) {
    }

    public function hasServiceZone(array $zone, ChannelInterface $channel): array
    {
        return $this->serviceRulesRepository->getServiceRulesByZoneAndChannel($zone, $channel);
    }

    public function hasServiceShippingCategory(ShippingCategory $shippingCategory, $serviceRulesId)
    {
        return $this->serviceRulesRepository->getServiceRulesByShippingCategory($shippingCategory, $serviceRulesId);
    }

    public function checkShippingPriorityService(array $shippingMethods, array $services): ?array
    {
        $formatAvailableShippingMethods = [];

        $servicesAndShippingMethods = array_merge($shippingMethods, $services);
        foreach ($servicesAndShippingMethods as $shippingMethod) {
            if (null !== $shippingMethod) {
                $formatAvailableShippingMethods[$shippingMethod['priority']] = $shippingMethod;
            }
        }
        ksort($formatAvailableShippingMethods);

        return $formatAvailableShippingMethods;
    }
}
