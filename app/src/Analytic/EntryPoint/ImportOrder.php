<?php

declare(strict_types=1);

namespace App\Analytic\EntryPoint;

use App\Analytic\Command\ImportOrderCommand;
use App\Doctrine\Entity\Sylius\Order;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ImportOrder
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $rimdianLogger
    ) {
    }

    public function sendOrder(Order $order): void
    {
        if (null === $order->getNumber() || null === $order->getId()) {
            return;
        }

        $this->rimdianLogger->info(sprintf('New Sylius -> Rimdian import single order with id %s', $order->getId()));

        $this->bus->dispatch(new ImportOrderCommand($order));
    }
}
