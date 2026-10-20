<?php

declare(strict_types=1);

namespace App\ERP\Application\Command\Return;

use App\Api\Odoo\Exception\OdooApiResponseException;
use App\Api\Odoo\Model\Order;
use App\ERP\Domain\Order\Repository\OrderRepositoryInterface;
use App\ERP\Domain\StateMachine\Applicator\Exception\TransitionFailException;
use App\ERP\Domain\StateMachine\Applicator\StateMachineApplicatorInterface;
use App\ERP\Infrastructure\Shared\Bus\Command\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class CreateReturnCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StateMachineApplicatorInterface $stateMachineApplicator,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function __invoke(CreateReturnCommand $command): void
    {
        $order = $this->orderRepository->get($command->orderId);
        try {
            $this->stateMachineApplicator->createReturn($order);
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        } catch (TransitionFailException $e) {
            if (str_contains($e->getMessage(), OdooApiResponseException::DUPLICATE_KEY_VALUE_VIOLATES_UNIQUE_CONSTRAINT)) {
                $order->setOdooState(Order::ODOO_STATE_CREATED);
                $this->entityManager->persist($order);
                $this->entityManager->flush();
            } else {
                throw $e;
            }
        }
    }
}
