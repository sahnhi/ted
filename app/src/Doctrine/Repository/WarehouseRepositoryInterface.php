<?php

namespace App\Doctrine\Repository;

use App\Doctrine\Entity\Warehouse;

interface WarehouseRepositoryInterface
{
    public function findDefault(): ?Warehouse;
}
