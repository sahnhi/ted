<?php

declare(strict_types=1);

namespace App\MessageHandler\Shipping;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\ShippingMethodCategoryService;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Repository\ShippingMethodCategoryRepository;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Model\SelectShippingMethodCategory;
use App\Modifier\Shipping\ShippingMethodCategoryServiceOrderModifier;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use SM\SMException;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;

class SelectShippingMethodCategoryHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly StateMachineFactoryInterface $stateMachineFactory,
        private readonly ShippingMethodCategoryProvider $shippingMethodCategoryProvider,
        private readonly ShippingMethodCategoryServiceOrderModifier $shippingMethodCategoryServiceOrderModifier,
        private readonly ShippingMethodCategoryRepository $shippingMethodCategoryRepository,
        private readonly OrderProcessorInterface $orderProcessor,
    ) {
    }

    /**
     * @throws SMException
     */
    public function __invoke(SelectShippingMethodCategory $selectShippingMethodCategory): Order
    {
        /** @var Order|null $cart */
        $cart = $this->orderRepository->findCartByTokenValue($selectShippingMethodCategory->getOrderTokenValue());

        Assert::notNull($cart, 'Cart has not been found.');

        $stateMachine = $this->stateMachineFactory->get($cart, OrderCheckoutTransitions::GRAPH);

        Assert::true(
            $stateMachine->can(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING),
            'Order cannot have shipment method assigned.'
        );

        $shippingMethodCategories = $this->shippingMethodCategoryProvider->provideEligibleForOrder($cart);

        if (!array_key_exists($selectShippingMethodCategory->shippingMethodCategoryCode, $shippingMethodCategories)) {
            throw new \InvalidArgumentException(sprintf('Shipping method category %s is not eligible for this cart.', $selectShippingMethodCategory->shippingMethodCategoryCode));
        }

        /**
         * The provider returns us a cloned ShippingMethodCategory, we therefore need to find the actual selected ShippingMethodCategory
         * after we know it's eligible.
         */
        /** @var ShippingMethodCategory $shippingMethodCategory */
        $shippingMethodCategory = $this->shippingMethodCategoryRepository->findOneBy([
            'code' => $selectShippingMethodCategory->shippingMethodCategoryCode,
        ]);

        /** @var Shipment $shipment */
        foreach ($cart->getShipments() as $shipment) {
            // We remove the services from the previous selected ShippingMethodCategory, if there's one
            if (null !== $shipment->getShippingMethodCategory()) {
                $this->shippingMethodCategoryServiceOrderModifier->removeServicesFromOrder($shipment->getShippingMethodCategory()->getEnabledServices(), $cart);
            }

            $shipment->setShippingMethodCategory($shippingMethodCategory);

            $shipment->setStartDeliverySlot(null);
            $shipment->setEndDeliverySlot(null);
        }

        // For the included services, we need to match the quantity to the applicable orderItems since the included
        // services are automatically added
        $this->shippingMethodCategoryServiceOrderModifier->addServicesToOrder(
            $shippingMethodCategory->getIncludedServices(),
            $cart,
            matchOrderItemQuantity: true
        );

        /** @var array<ShippingMethodCategoryService> $servicesByCode */
        $servicesByCode = [];
        foreach ($shippingMethodCategory->getPaidServices() as $availablePaidService) {
            $servicesByCode[$availablePaidService->getCode()] = $availablePaidService;
        }

        $selectedServices = [];
        foreach ($selectShippingMethodCategory->serviceCodes as $serviceCode) {
            $matchedService = $servicesByCode[$serviceCode] ?? null;

            if (null !== $matchedService) {
                $selectedServices[] = $matchedService;
            }
        }

        $this->shippingMethodCategoryServiceOrderModifier->addServicesToOrder($selectedServices, $cart);

        $this->orderProcessor->process($cart);

        return $cart;
    }
}
