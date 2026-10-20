<?php

namespace App\Doctrine\Repository\Sylius;

use App\Doctrine\Entity\Sylius\ShipmentInterface;
use Sylius\Component\Core\Repository\ShipmentRepositoryInterface as SyliusShipmentRepository;

interface ShipmentRepositoryInterface extends SyliusShipmentRepository
{
    public function findOneByOrderNumber(string $orderNumber): ?ShipmentInterface;
}
