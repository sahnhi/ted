<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class ReturnOrderReasonItemsProcessor implements ReorderProcessor
{
    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        foreach ($reorder->getOrderReason() as $key => $orderReason) {
            Assert::notNull($orderReason->getReason(), 'Reason cannot be null.');
            if ($orderReason->getReason()) {
                if (isset($reorder->getItems()[$key])) {
                    $orderReason->setQuantity($reorder->getItems()[$key]->getQuantity());
                    $orderReason->setVariant($reorder->getItems()[$key]->getVariant());
                }
            }
        }
    }
}
