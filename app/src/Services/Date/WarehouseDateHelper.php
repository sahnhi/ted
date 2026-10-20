<?php

namespace App\Services\Date;

use App\Doctrine\Entity\ExceptionalSchedule;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Warehouse;
use Doctrine\ORM\EntityRepository;

class WarehouseDateHelper implements WarehouseDateHelperInterface
{
    public function __construct(
        private readonly EntityRepository $shippingMethodRepository,
        private readonly EntityRepository $exceptionalScheduleRepository
    ) {
    }

    public function getWarehouse(?string $method): ?Warehouse
    {
        $warehouse = null;

        /** @var ShippingMethod $method */
        $method = $this->shippingMethodRepository->findOneBy(['code' => $method]);

        if (null !== $method) {
            $warehouse = $method->getWarehouse();
        }

        return $warehouse;
    }

    public function isDateAvailable(Warehouse $warehouse, \DateTime $date): bool
    {
        $scheduleType = $this->getExceptionalScheduleType($warehouse, $date);
        $isOpen = in_array($date->format('w'), $warehouse->getOpeningCalendarDays());

        if (null !== $scheduleType) {
            if (true === $isOpen) {
                $isOpen = ExceptionalSchedule::EXCEPTIONAL_TYPE_CLOSING !== $scheduleType;
            } else {
                $isOpen = ExceptionalSchedule::EXCEPTIONAL_TYPE_OPENING === $scheduleType;
            }
        }

        return $isOpen;
    }

    public function getFirstPreviousDateAvailable(Warehouse $warehouse, \DateTime $date): \DateTime
    {
        $interval = new \DateInterval('P1D');
        $yesterday = clone $date;
        $yesterday->sub($interval);

        return $this->isDateAvailable($warehouse, $yesterday) ? $yesterday : $this->getFirstPreviousDateAvailable($warehouse, $yesterday);
    }

    public function getExceptionalScheduleType(Warehouse $warehouse, \DateTime $date): ?string
    {
        /** @var ExceptionalSchedule $schedule */
        $schedule = $this->exceptionalScheduleRepository->getOneByWarehouseAndDate($warehouse, $date);

        return null === $schedule ? null : $schedule->getType();
    }
}
