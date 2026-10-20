<?php

declare(strict_types=1);

namespace App\Doctrine\Repository;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderItemLogisticRepositoryInterface
{
    public function findByOrder(OrderInterface $order): array;
}
