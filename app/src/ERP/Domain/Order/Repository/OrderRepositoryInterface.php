<?php

namespace App\ERP\Domain\Order\Repository;

use App\Doctrine\Entity\Sylius\Order;

interface OrderRepositoryInterface
{
    public function get(int $id): ?Order;
}
