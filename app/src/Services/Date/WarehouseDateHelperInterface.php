<?php

namespace App\Services\Date;

use App\Doctrine\Entity\Warehouse;
use Doctrine\ORM\EntityRepository;

interface WarehouseDateHelperInterface
{
    public function __construct(EntityRepository $shippingMethodRepository, EntityRepository $exceptionalScheduleRepository);

    public function getWarehouse(?string $method): ?Warehouse;

    public function getFirstPreviousDateAvailable(Warehouse $warehouse, \DateTime $date): \DateTime;

    public function isDateAvailable(Warehouse $warehouse, \DateTime $date): bool;

    public function getExceptionalScheduleType(Warehouse $warehouse, \DateTime $date): ?string;
}
