<?php

namespace App\Services\OutOfStock;

use App\Doctrine\Entity\OrderOutOfStockInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;

class WeightingCalculation implements WeightingCalculationInterface
{
    public function recalculate(Order $order, ?Order $parent): int
    {
        $date = new \DateTime();

        $checkout = null !== $parent ? $parent->getCheckoutCompletedAt() : $order->getCheckoutCompletedAt();
        /** @var Shipment $shipment */
        $shipment = $order->getShipments()->first();

        if (null !== $parent
            && false !== $parent->getShipments()->first()
            && null !== $parent->getShipments()->first()->getStartDeliverySlot()
        ) {
            $shipment = $parent->getShipments()->first();
        }

        $weighting = (int) date_diff($checkout, $date)->format('%a');

        if ($shipment && null !== $shipment->getStartDeliverySlot() && $shipment->getStartDeliverySlot() < $date) {
            $weighting += (int) date_diff($shipment->getStartDeliverySlot(), $date)->format('%a');
        }

        foreach ($order->getShipments() as $shipment) {
            $weighting += ceil($shipment->getMethod()->getTransportTime());
        }

        if (null !== $order->getOrderVip()) {
            $weighting += 50;
        }

        if (1500 < $order->getTotal()) {
            $weighting += 10;
        }

        if (null !== $order->getOrderOutOfStock() && OrderOutOfStockInterface::STATE_PARTIALLY_PLANNED === $order->getOrderOutOfStock()->getStatus()) {
            $weighting += 100;
        }

        return $weighting;
    }
}
