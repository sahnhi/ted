<?php

namespace App\ViewRepository;

use App\Doctrine\Repository\Sylius\OrderRepository;

final class CustomerViewRepository implements CustomerViewRepositoryInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository
    ) {
    }

    public function isNewCustomer(string $customerId, string $orderToken): bool
    {
        return $this->orderRepository->isNewCustomer($customerId, $orderToken);
    }
}
