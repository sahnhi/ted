<?php

namespace App\Handler;

use App\Api\Odoo\Message\Return\CreateReturn;
use App\Business\Command\ChooseShipping;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Provider\Shipping\EligibleShippingMethodProvider;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use App\Services\Channel\ChannelCode;
use App\Services\Shipment\ShipmentService;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ChooseShippingHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly FactoryInterface $stateMachineFactory,
        private readonly TranslatorInterface $translator,
        private readonly ShipmentService $shipmentService,
        private readonly MessageBusInterface $messageBus,
        private readonly ShippingMethodCategoryProvider $shippingMethodCategoryProvider,
        private readonly EligibleShippingMethodProvider $eligibleShippingMethodProvider,
    ) {
    }

    /**
     * @throws SMException
     */
    public function handle(ChooseShipping $chooseShipping): Order
    {
        /** @var Order|null $cart */
        $cart = $this->orderRepository->findCartByTokenValue($chooseShipping->getOrderToken());
        if (null === $cart) {
            throw new \InvalidArgumentException($this->translator->trans('app.api.order.shipping.error.cart_not_found'));
        }

        $eligibleShippingMethodCategories = $cart->getShippingMethodCategories();
        if ([] === $eligibleShippingMethodCategories) {
            $eligibleShippingMethodCategories = $this->shippingMethodCategoryProvider->provideEligibleForOrder($cart);
        }

        $eligibleShippingMethodsByCode = [];
        foreach ($eligibleShippingMethodCategories as $shippingMethodCategory) {
            $eligibleShippingMethods = $this->eligibleShippingMethodProvider->provideEligibleShippingMethods($shippingMethodCategory, $cart->getShipments(), $cart->getChannel());
            foreach ($eligibleShippingMethods as $eligibleShippingMethod) {
                $eligibleShippingMethodsByCode[$eligibleShippingMethod->getCode()] = $eligibleShippingMethod;
            }
        }

        if (!array_key_exists($chooseShipping->getMethod(), $eligibleShippingMethodsByCode)) {
            throw new \InvalidArgumentException($this->translator->trans('app.api.order.shipping.error.shipment_not_eligible'));
        }

        $shippingMethod = $eligibleShippingMethodsByCode[$chooseShipping->getMethod()];

        foreach ($cart->getShipments() as $shipment) {
            $this->shipmentService->chooseShippingMethod($cart, $shipment, $shippingMethod, $chooseShipping);
        }

        $stateMachine = $this->stateMachineFactory->get($cart, OrderCheckoutTransitions::GRAPH);
        if (!$stateMachine->can(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)) {
            throw new \InvalidArgumentException($this->translator->trans('app.api.order.shipping.error.cannot_proceed'));
        }

        $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING);

        if (Reason::RETURN_TYPE_AUTONOMOUS === $cart->getReturnType() && ChannelCode::CHANNEL_RETURN === $cart->getChannel()?->getCode()) {
            $this->messageBus->dispatch(new CreateReturn($cart->getId()));
        }

        return $cart;
    }
}
