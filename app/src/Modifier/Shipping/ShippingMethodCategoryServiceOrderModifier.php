<?php

declare(strict_types=1);

namespace App\Modifier\Shipping;

use App\Doctrine\Entity\ShippingMethodCategoryService;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Enum\AdjustmentTypeEnum;
use App\Exception\ChannelPricingNotFoundException;
use App\Resolver\Shipping\ShippingMethodCategoryServiceQuantityResolver;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ShippingMethodCategoryServiceOrderModifier
{
    public const ADJUSTMENT_SERVICE_CODE_DETAILS_INDEX = 'serviceCode';

    public function __construct(
        private readonly FactoryInterface $orderItemFactory,
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
        private readonly OrderModifierInterface $orderModifier,
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private readonly ShippingMethodCategoryServiceQuantityResolver $serviceEligibilityChecker,
    ) {
    }

    /**
     * @param iterable<ShippingMethodCategoryService> $services
     */
    public function addServicesToOrder(iterable $services, OrderInterface $order, bool $matchOrderItemQuantity = false): void
    {
        /** @var Channel $channel */
        $channel = $order->getChannel();

        foreach ($services as $service) {
            // A service is considered eligible if it can be added at lease one time
            if (0 === ($serviceQuantity = $this->serviceEligibilityChecker->resolveServiceQuantity($service, $order))) {
                continue;
            }

            $quantity = $matchOrderItemQuantity ? $serviceQuantity : 1;

            try {
                $orderItem = $this->createOrderItem($service, $channel, $quantity);
            } catch (ChannelPricingNotFoundException) {
                continue;
            }

            $this->orderModifier->addToOrder($order, $orderItem);
        }
    }

    /**
     * @param iterable<ShippingMethodCategoryService> $services
     */
    public function removeServicesFromOrder(iterable $services, OrderInterface $order): void
    {
        /** @var ShippingMethodCategoryService $service */
        foreach ($services as $service) {
            foreach ($order->getServiceItems() as $serviceItem) {
                if ($serviceItem->getVariant() !== $service->getProductVariant()) {
                    continue;
                }

                $this->orderModifier->removeFromOrder($order, $serviceItem);
            }
        }
    }

    /**
     * @throws ChannelPricingNotFoundException
     */
    protected function createOrderItem(ShippingMethodCategoryService $service, Channel $channel, int $quantity = 1): OrderItem
    {
        $variant = $service->getProductVariant();

        $channelPricing = $variant->getChannelPricingForChannel($channel);
        if (null === $channelPricing) {
            throw new ChannelPricingNotFoundException(sprintf('Channel pricing for %s not found.', $channel->getCode()));
        }

        /** @var OrderItem $orderItem */
        $orderItem = $this->orderItemFactory->createNew();
        $orderItem->setVariant($variant);
        $orderItem->setUnitPrice($channelPricing->getPrice());

        $this->orderItemQuantityModifier->modify($orderItem, $quantity);

        // This adjustment is here to flag an order item that was added from a ShippingMethodCategoryService
        $flagAdjustment = $this->adjustmentFactory->createWithData(
            type: AdjustmentTypeEnum::SHIPPING_METHOD_SERVICE->value,
            label: AdjustmentTypeEnum::SHIPPING_METHOD_SERVICE->getLabel(),
            amount: 0,
            neutral: true,
            details: [
                self::ADJUSTMENT_SERVICE_CODE_DETAILS_INDEX => $service->getCode(),
            ]
        );

        $orderItem->addAdjustment($flagAdjustment);

        if ($service->isIncluded()) {
            /** @var OrderItemUnit $unit */
            foreach ($orderItem->getUnits() as $unit) {
                $unit->addAdjustment($this->adjustmentFactory->createWithData(
                    type: AdjustmentTypeEnum::OFFERED_SERVICE->value,
                    label: AdjustmentTypeEnum::OFFERED_SERVICE->getLabel(),
                    amount: -$orderItem->getUnitPrice(),
                ));
            }
        }

        return $orderItem;
    }
}
