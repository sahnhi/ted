<?php

namespace App\Doctrine\Repository\Sylius;

use Sylius\Component\Core\Repository\CustomerRepositoryInterface as SyliusCustomerRepositoryInterface;

interface CustomerRepositoryInterface extends SyliusCustomerRepositoryInterface
{
    public function findCustomerIds(int $offset, int $limit): array;
}
