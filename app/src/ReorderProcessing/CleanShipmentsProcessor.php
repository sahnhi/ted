<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;

final class CleanShipmentsProcessor implements ReorderProcessor
{
    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        foreach ($reorder->getShipments() as $key => $shipment) {
            if (0 !== $key) {
                $reorder->removeShipment($shipment);
            }
        }
    }
}
