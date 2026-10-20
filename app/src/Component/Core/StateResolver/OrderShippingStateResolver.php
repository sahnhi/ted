<?php

declare(strict_types=1);

namespace App\Component\Core\StateResolver;

use App\Component\Core\OrderShippingStates;
use App\Component\Core\OrderShippingTransitions;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\StateResolver\StateResolverInterface;
use Webmozart\Assert\Assert;

final class OrderShippingStateResolver implements StateResolverInterface
{
    public function __construct(
        private readonly FactoryInterface $stateMachineFactory
    ) {
    }

    public function resolve(BaseOrderInterface $order): void
    {
        /* @var OrderInterface $order */
        Assert::isInstanceOf($order, OrderInterface::class);

        if (OrderShippingStates::STATE_DELIVERED === $order->getShippingState()) {
            return;
        }
        /** @var StateMachineInterface $stateMachine */
        $stateMachine = $this->stateMachineFactory->get($order, OrderShippingTransitions::GRAPH);

        if ($this->allShipmentsInStateButOrderStateNotUpdated($order, OrderShippingStates::STATE_DELIVERED, OrderShippingStates::STATE_DELIVERED)) {
            $stateMachine->apply(OrderShippingTransitions::TRANSITION_DELIVER);
        }
    }

    private function countOrderShipmentsInState(OrderInterface $order, string $shipmentState): int
    {
        $shipments = $order->getShipments();

        return $shipments
            ->filter(fn (ShipmentInterface $shipment): bool => $shipment->getState() === $shipmentState)
            ->count()
        ;
    }

    private function allShipmentsInStateButOrderStateNotUpdated(
        OrderInterface $order,
        string $shipmentState,
        string $orderShippingState
    ): bool {
        $shipmentInStateAmount = $this->countOrderShipmentsInState($order, $shipmentState);
        $shipmentAmount = $order->getShipments()->count();

        return $shipmentAmount === $shipmentInStateAmount && $orderShippingState !== $order->getShippingState();
    }
}
