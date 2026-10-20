<?php

declare(strict_types=1);

namespace App\ERP\Application\Command;

use App\Api\Odoo\Exception\OdooApiResponseException;
use App\Api\Odoo\Model\Order;
use App\ERP\Domain\Order\Repository\OrderRepositoryInterface;
use App\ERP\Domain\StateMachine\Applicator\Exception\TransitionFailException;
use App\ERP\Domain\StateMachine\Applicator\StateMachineApplicatorInterface;
use App\ERP\Infrastructure\Shared\Bus\Command\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class CancelCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StateMachineApplicatorInterface $stateMachineApplicator,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function __invoke(CancelCommand $command): void
    {
        $order = $this->orderRepository->get($command->orderId);
        try {
            $this->stateMachineApplicator->cancelOrder($order);
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        } catch (TransitionFailException $e) {
            if (
                str_contains($e->getMessage(), OdooApiResponseException::ORDER_ALREADY_CANCELLED)
                || str_contains($e->getMessage(), OdooApiResponseException::ORDER_ALREADY_VALIDATED)
            ) {
                $order->setOdooState(Order::ODOO_STATE_CANCELLED);
                $this->entityManager->persist($order);
                $this->entityManager->flush();
            } else {
                throw $e;
            }
        }
    }
}
