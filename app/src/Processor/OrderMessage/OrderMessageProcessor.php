<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

final class OrderMessageProcessor implements OrderProcessorInterface
{
    public function __construct(
        private readonly iterable $processors
    ) {
    }

    /**
     * @param Order $order
     */
    public function process(ResourceInterface $order): void
    {
        if (!$order instanceof OrderInterface) {
            throw new \InvalidArgumentException('Order must be an instance of ' . OrderInterface::class);
        }

        $order->clearMessages();

        foreach ($this->processors as $processor) {
            $processor->process($order);
        }
    }
}
