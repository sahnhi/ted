<?php

declare(strict_types=1);

namespace App\Provider\Shipping;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\Sylius\Zone;
use App\Doctrine\Repository\ShippingMethodCategoryRepository;
use App\Factory\OrderFactoryInterface;
use App\Resolver\Shipping\ShippingMethodCategoryServiceQuantityResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\Scope;

class ShippingMethodCategoryProvider
{
    public function __construct(
        private readonly ShippingMethodCategoryRepository $shippingMethodCategoryRepository,
        private readonly EligibleShippingMethodProvider $eligibleShippingMethodProvider,
        private readonly ZoneMatcherInterface $zoneMatcher,
        private readonly OrderFactoryInterface $orderFactory,
        private readonly ShippingMethodCategoryServiceQuantityResolver $shippingMethodCategoryServiceQuantityResolver,
    ) {
    }

    /**
     * @param array<ZoneInterface>|null $zones
     *
     * @return array<ShippingMethodCategory>
     */
    protected function provideForOrder(Order $order, ChannelInterface $channel, array|null $zones = null): array
    {
        $shippingMethodCategories = match ($zones) {
            null => $this->shippingMethodCategoryRepository->findEnabledForChannel($channel),
            default => $this->shippingMethodCategoryRepository->findEnabledForZonesAndChannel($zones, $channel)
        };

        if ([] === $shippingMethodCategories) {
            return [];
        }

        $shipments = $order->getShipments();

        $availableShippingMethodCategories = [];
        foreach ($shippingMethodCategories as $shippingMethodCategory) {
            // We need to clone since we are going to remove un-eligible shipping methods from the...
            // ...ShippingMethodCategory's ShippingMethods
            $clonedShippingMethodCategory = clone $shippingMethodCategory;

            $eligibleShippingMethods = $this->eligibleShippingMethodProvider->provideEligibleShippingMethods(
                $clonedShippingMethodCategory,
                $shipments,
                $channel,
            );

            if ($eligibleShippingMethods->isEmpty()) {
                continue;
            }

            $distinctServices = new ArrayCollection();
            foreach ($clonedShippingMethodCategory->getEnabledServices() as $enabledService) {
                $availableServiceQuantity = $this->shippingMethodCategoryServiceQuantityResolver->resolveServiceQuantity($enabledService, $order);
                // An included service is returned only once, since the quantity is automatically calculated when
                // selecting the associated ShippingMethodCategory
                if ($availableServiceQuantity > 0 && $enabledService->isIncluded()) {
                    $distinctServices->add($enabledService);
                    continue;
                }

                for ($i = 0; $i < $availableServiceQuantity; ++$i) {
                    $distinctServices->add($enabledService);
                }
            }

            $clonedShippingMethodCategory->setShippingMethodCategoryServices($distinctServices);

            $clonedShippingMethodCategory->setShippingMethods($eligibleShippingMethods);

            $availableShippingMethodCategories[$clonedShippingMethodCategory->getCode()] = $clonedShippingMethodCategory;
        }

        return $availableShippingMethodCategories;
    }

    /**
     * @return array<ShippingMethodCategory>
     */
    public function provideEligibleForOrder(OrderInterface $order): array
    {
        $shippingAddress = $order->getShippingAddress();

        $zones = null;
        if (null !== $shippingAddress) {
            $zones = $this->zoneMatcher->matchAll($shippingAddress, Scope::SHIPPING);
        }

        return $this->provideForOrder($order, $order->getChannel(), $zones);
    }

    /**
     * @param array<Zone>|null $zones
     *
     * @return array<ShippingMethodCategory>
     */
    public function provideEligibleForProductVariantsAnZones(
        array $variants,
        ChannelInterface $channel,
        array|null $zones
    ): array {
        $order = $this->orderFactory->createNewForChargesEstimate($variants, $channel);

        return $this->provideForOrder($order, $channel, $zones);
    }
}
