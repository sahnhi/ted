<?php

namespace App\Services\Olvo;

use App\Doctrine\Entity\Sylius\OrderItem;

interface PackageTypeResolverInterface
{
    /**
     * @throws PackageTypeNotFoundException
     */
    public function resolvePackageType(OrderItem $orderItem): int;
}
