<?php

namespace App\Services\Calendar;

use Sylius\Component\Resource\Repository\RepositoryInterface;

class ExceptionalScheduleService
{
    public function __construct(
        private readonly RepositoryInterface $exceptionalScheduleRepository
    ) {
    }

    public function check(array $shippingMethods): array
    {
        foreach ($shippingMethods as &$shippingMethod) {
            /* Check if it is a ShippingMethod or a serviceRules type */
            if (1 === $shippingMethod['isShippingMethod']) {
                $exceptionalSchedule = $this->exceptionalScheduleRepository->findBy(['shippingMethod' => $shippingMethod['id']]);
            } else {
                $exceptionalSchedule = $this->exceptionalScheduleRepository->findBy(['serviceRules' => $shippingMethod['id']]);
            }
            foreach ($exceptionalSchedule as $schedule) {
                $shippingMethod[$schedule->getType() . '_schedule'][] = [
                    'start' => $schedule->getStart(),
                    'end' => $schedule->getEnd(),
                    'calendarDays' => $schedule->getCalendarDays(),
                ];
            }

            if (null !== $shippingMethod['warehouse_id']) {
                $warehouseSchedule = $this->exceptionalScheduleRepository->findBy(['warehouse' => $shippingMethod['warehouse_id']]);

                foreach ($warehouseSchedule as $schedule) {
                    $shippingMethod['warehouse_' . $schedule->getType() . '_schedule'][] = [
                        'start' => $schedule->getStart(),
                        'end' => $schedule->getEnd(),
                        'calendarDays' => $schedule->getCalendarDays(),
                    ];
                }
            }
        }

        return $shippingMethods;
    }
}
