<?php

namespace App\Services\Shipment;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface LogisticItemsInterface
{
    public function getLogisticItemsComboFromOrder(OrderInterface $order, RepositoryInterface $variantRepository, RepositoryInterface $comboRepository, ?OrderInterface $parentOrder = null): array;
}
