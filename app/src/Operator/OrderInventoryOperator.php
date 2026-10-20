<?php

namespace App\Operator;

use Sylius\Component\Core\Inventory\Operator\OrderInventoryOperatorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Webmozart\Assert\Assert;

final class OrderInventoryOperator implements OrderInventoryOperatorInterface
{
    public function cancel(OrderInterface $order): void
    {
        if (in_array(
            $order->getPaymentState(),
            [OrderPaymentStates::STATE_PAID, OrderPaymentStates::STATE_REFUNDED],
            true
        )) {
            $this->release($order);

            return;
        }
    }

    public function hold(OrderInterface $order): void
    {
        /** @var OrderItemInterface $orderItem */
        foreach ($order->getItems() as $orderItem) {
            $variant = $orderItem->getVariant();

            if (!$variant->isTracked()) {
                continue;
            }

            $variant->setOnHold($variant->getOnHold() + $orderItem->getQuantity());
        }
    }

    public function sell(OrderInterface $order): void
    {
        /** @var OrderItemInterface $orderItem */
        foreach ($order->getItems() as $orderItem) {
            $variant = $orderItem->getVariant();

            if (!$variant->isTracked()) {
                continue;
            }

            /* Custom remove Assert::greaterThanEq verification from original file : Sylius\Component\Core\Inventory\Operator\OrderInventoryOperator */

            $variant->setOnHold($variant->getOnHold() - $orderItem->getQuantity());
            $variant->setOnHand($variant->getOnHand() - $orderItem->getQuantity());
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function release(OrderInterface $order): void
    {
        /** @var OrderItemInterface $orderItem */
        foreach ($order->getItems() as $orderItem) {
            $variant = $orderItem->getVariant();

            if (!$variant->isTracked()) {
                continue;
            }

            $variant->setOnHold($variant->getOnHold() - $orderItem->getQuantity());
        }
    }

    private function giveBack(OrderInterface $order): void
    {
        /** @var OrderItemInterface $orderItem */
        foreach ($order->getItems() as $orderItem) {
            $variant = $orderItem->getVariant();

            if (!$variant->isTracked()) {
                continue;
            }

            $variant->setOnHand($variant->getOnHand() + $orderItem->getQuantity());
        }
    }
}
