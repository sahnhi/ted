<?php

declare(strict_types=1);

namespace App\Processor;

use App\Doctrine\Entity\ShippingMethodCategoryService;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Enum\AdjustmentTypeEnum;
use App\Modifier\Shipping\ShippingMethodCategoryServiceOrderModifier;
use App\Resolver\Shipping\ShippingMethodCategoryServiceQuantityResolver;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class EligibleShippingMethodCategoryServicesProcessor implements OrderProcessorInterface
{
    public function __construct(
        private readonly RepositoryInterface $shippingMethodCategoryServiceRepository,
        private readonly ShippingMethodCategoryServiceQuantityResolver $shippingMethodCategoryServiceQuantityResolver,
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
    ) {
    }

    /**
     * @param OrderInterface $order
     */
    public function process(BaseOrderInterface $order): void
    {
        if (!$order instanceof OrderInterface) {
            return;
        }

        $orderShippingMethodCategories = $order->getShippingMethodCategories();
        if ([] === $orderShippingMethodCategories) {
            return;
        }

        foreach ($order->getServiceItems() as $orderServiceItem) {
            /** @var AdjustmentInterface|false $flagAdjustment */
            $flagAdjustment = $orderServiceItem->getAdjustments(AdjustmentTypeEnum::SHIPPING_METHOD_SERVICE->value)->first();
            if (false === $flagAdjustment) {
                continue;
            }

            $adjustmentDetails = $flagAdjustment->getDetails();
            if (!isset($adjustmentDetails[ShippingMethodCategoryServiceOrderModifier::ADJUSTMENT_SERVICE_CODE_DETAILS_INDEX])) {
                continue;
            }

            $serviceCode = $adjustmentDetails[ShippingMethodCategoryServiceOrderModifier::ADJUSTMENT_SERVICE_CODE_DETAILS_INDEX];

            /** @var ShippingMethodCategoryService|null $service */
            $service = $this->shippingMethodCategoryServiceRepository->findOneBy(['code' => $serviceCode, 'enabled' => true]);
            // If the service is not found, OR it isn't for the selected category : we remove it
            if (null === $service || !in_array($service->getShippingMethodCategory(), $orderShippingMethodCategories, true)) {
                $order->removeItem($orderServiceItem);
                continue;
            }

            $eligibleServiceQuantity = $this->shippingMethodCategoryServiceQuantityResolver->resolveServiceQuantity($service, $order);
            if (0 === $eligibleServiceQuantity) {
                $order->removeItem($orderServiceItem);
                continue;
            }

            // The quantity of an included service should always match the eligible quantity
            if ($service->isIncluded() && $eligibleServiceQuantity !== $orderServiceItem->getQuantity()) {
                $this->orderItemQuantityModifier->modify($orderServiceItem, $eligibleServiceQuantity);
                continue;
            }

            // If a service is not included, and we have more than allowed, we lower the quantity to match the allowed one
            if (!$service->isIncluded() && $orderServiceItem->getQuantity() > $eligibleServiceQuantity) {
                $this->orderItemQuantityModifier->modify($orderServiceItem, $eligibleServiceQuantity);
            }
        }
    }
}
