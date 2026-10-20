<?php

declare(strict_types=1);

namespace App\ERP\Application\Command\Order;

use App\Api\Odoo\Exception\OdooApiResponseException;
use App\Api\Odoo\Model\Order;
use App\ERP\Domain\Order\Repository\OrderRepositoryInterface;
use App\ERP\Domain\StateMachine\Applicator\Exception\TransitionFailException;
use App\ERP\Domain\StateMachine\Applicator\StateMachineApplicatorInterface;
use App\ERP\Infrastructure\Shared\Bus\Command\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class ShipOrderCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StateMachineApplicatorInterface $stateMachineApplicator,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function __invoke(ShipOrderCommand $command): void
    {
        $order = $this->orderRepository->get($command->orderId);
        try {
            $this->stateMachineApplicator->shipOrder($order);
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        } catch (TransitionFailException $e) {
            if (str_contains($e->getMessage(), OdooApiResponseException::MULTIPLE_PICKINGS_FOUND)) {
                $order->setOdooState(Order::ODOO_STATE_SHIPPED);
                $this->entityManager->persist($order);
                $this->entityManager->flush();
            } else {
                throw $e;
            }
        }
    }
}
