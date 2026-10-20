<?php

declare(strict_types=1);

namespace App\Api\Odoo\MessageHandler\Order;

use App\Api\Odoo\Message\Order\CreateOrder;
use App\Doctrine\Entity\Sylius\Order as OrderEntity;
use App\ERP\Application\Command\CommandBusInterface;
use App\ERP\Application\Command\Order\CreateOrderCommand;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;

final class CreateOrderHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(CreateOrder $message): void
    {
        $order = $this->orderRepository->find($message->orderId);
        Assert::isInstanceOf($order, OrderEntity::class, 'Order not found for id ' . $message->orderId);
        $this->commandBus->handle(new CreateOrderCommand($order->getId()));
    }
}
