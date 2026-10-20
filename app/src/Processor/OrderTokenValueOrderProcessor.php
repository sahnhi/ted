<?php

declare(strict_types=1);

namespace App\Processor;

use App\Doctrine\Entity\Sylius\Order;
use Sylius\Component\Core\TokenAssigner\UniqueIdBasedOrderTokenAssigner;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;

class OrderTokenValueOrderProcessor implements OrderProcessorInterface
{
    public function __construct(
        private readonly UniqueIdBasedOrderTokenAssigner $uniqueIdBasedOrderTokenAssigner
    ) {
    }

    /**
     * @param Order $order
     */
    public function process(OrderInterface $order): void
    {
        $this->uniqueIdBasedOrderTokenAssigner->assignTokenValueIfNotSet($order);
    }
}
