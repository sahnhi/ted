<?php

declare(strict_types=1);

namespace App\Api\Odoo\MessageHandler\Return;

use App\Api\Odoo\Message\Return\CreateReturn;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\ERP\Application\Command\CommandBusInterface;
use App\ERP\Application\Command\Return\CreateReturnCommand;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Webmozart\Assert\Assert;

final class CreateReturnHandler implements MessageHandlerInterface
{
    /**
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(CreateReturn $message): void
    {
        $order = $this->orderRepository->find($message->orderId);
        Assert::isInstanceOf($order, OrderInterface::class, 'Order not found for id ' . $message->orderId);
        $this->commandBus->handle(new CreateReturnCommand($order->getId()));
    }
}
