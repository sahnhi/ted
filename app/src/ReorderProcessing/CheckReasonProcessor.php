<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class CheckReasonProcessor implements ReorderProcessor
{
    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        $orderReasons = $reorder->getOrderReason();

        if (!empty($orderReasons)) {
            foreach ($orderReasons as $orderReason) {
                Assert::notNull($orderReason->getReason(), 'Reason cannot be null.');
            }
        }
    }
}
