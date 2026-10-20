<?php

declare(strict_types=1);

namespace App\ERP\Infrastructure\Bridge\Doctrine;

use App\Doctrine\Entity\Sylius\Order;
use App\ERP\Domain\Order\Repository\OrderRepositoryInterface;

final class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly \App\Doctrine\Repository\Sylius\OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function get(int $id): ?Order
    {
        return $this->orderRepository->find($id);
    }
}
