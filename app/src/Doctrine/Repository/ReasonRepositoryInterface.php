<?php

namespace App\Doctrine\Repository;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface ReasonRepositoryInterface extends RepositoryInterface
{
    public function findForLogisticProductsInOrder(OrderInterface $order): array;
}
