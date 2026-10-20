<?php

namespace App\ViewRepository;

interface CustomerViewRepositoryInterface
{
    public function isNewCustomer(string $customerId, string $orderToken): bool;
}
