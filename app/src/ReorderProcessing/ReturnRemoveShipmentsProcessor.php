<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;

final class ReturnRemoveShipmentsProcessor implements ReorderProcessor
{
    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        if ($reorder->hasShipments()) {
            foreach ($reorder->getShipments() as $shipment) {
                foreach ($shipment->getAdjustments() as $adjustment) {
                    $shipment->removeAdjustment($adjustment);
                }

                foreach ($shipment->getUnits() as $unit) {
                    $shipment->removeUnit($unit);
                }

                $reorder->removeShipment($shipment);
            }
        }
    }
}
