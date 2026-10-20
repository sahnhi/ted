<?php

declare(strict_types=1);

namespace App\ERP\Application\Command\Return;

use App\ERP\Domain\Order\Repository\OrderRepositoryInterface;
use App\ERP\Domain\StateMachine\Applicator\StateMachineApplicatorInterface;
use App\ERP\Infrastructure\Shared\Bus\Command\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class ConfirmReturnCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StateMachineApplicatorInterface $stateMachineApplicator,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function __invoke(ConfirmReturnCommand $command): void
    {
        $order = $this->orderRepository->get($command->orderId);
        $this->stateMachineApplicator->confirmReturn($order);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }
}
