<?php

declare(strict_types=1);

namespace App\Api\Odoo\MessageHandler;

use App\Api\Odoo\Message\Cancel;
use App\Doctrine\Entity\Sylius\Order as OrderEntity;
use App\ERP\Application\Command\CancelCommand;
use App\ERP\Application\Command\CommandBusInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;

final class CancelHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(Cancel $message): void
    {
        $order = $this->orderRepository->find($message->orderId);
        Assert::isInstanceOf($order, OrderEntity::class, 'Order not found for id ' . $message->orderId);
        $this->commandBus->handle(new CancelCommand($order->getId()));
    }
}
