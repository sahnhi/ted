<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;

class ShippingAdjustmentCleanerProcessor implements ReorderProcessor
{
    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        $reorder->removeAdjustmentsRecursively(AdjustmentInterface::SHIPPING_ADJUSTMENT);
    }
}
