<?php

namespace App\Services\Calendar;

use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Doctrine\Entity\Sylius\Zone;
use App\Doctrine\Entity\ZipCode;
use App\Request\CalendarAvailabilityRequest;
use App\Services\Calendar\Maker\DeliverySlotMaker;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class CalendarApi
{
    private array $calendarDeliverySlot = [];

    private array $shippingMethods;

    private CalendarAvailabilityRequest $request;

    public function getRequest(): CalendarAvailabilityRequest
    {
        return $this->request;
    }

    public function setRequest(CalendarAvailabilityRequest $request): CalendarApi
    {
        $this->request = $request;

        return $this;
    }

    public function __construct(
        private readonly VariantService $variantService,
        private readonly ZoneService $zoneService,
        private readonly ExceptionalScheduleService $exceptionalScheduleService,
        private readonly ShippingCategoryService $shippingCategoryService,
        private readonly DeliverySlotMaker $deliverySlotMaker,
        private readonly VirTransportPlanService $virTransportPlanService,
        private readonly RepositoryInterface $zipCodeRepository,
    ) {
    }

    public static function isShortSlot(array $shippingMethod): bool
    {
        $delay = $shippingMethod['transport_time'];
        $cutOff = null !== $shippingMethod['warehouse_opening_calendarDays'] ?
            $shippingMethod['warehouse_cut_off'] :
            new \DateTime('11:30:00'); // need olvo entrepot

        $cutOffInMinutes = ((int) $cutOff->format('h') * 60) + ((int) $cutOff->format('i'));
        $delayInMinutes = (24 * $delay) * 60;

        // 1440 is the number of minutes in one day
        return 1440 > $delayInMinutes + $cutOffInMinutes;
    }

    /**
     * @throws \Exception
     */
    public function checkCalendar(): array
    {
        $this->variantService->setRequest($this->request);
        $checkVariant = $this->variantService->check();
        /*
         * Check zone eligibility and services priority
         */
        $this->zoneService->setRequest($this->request);
        $formattedShippingMethods = $this->zoneService->provideFormattedShippingMethods();

        /*
         * Check shippingCategory eligibility and services priority
         */
        $this->shippingCategoryService->setRequest($this->request);
        $checkShippingCategory = $this->shippingCategoryService->check($formattedShippingMethods);

        /**
         * Check exceptionalSchedule.
         */
        $checkExceptionalSchedule = $this->exceptionalScheduleService->check($checkShippingCategory);
        $this->shippingMethods = $checkExceptionalSchedule;

        $messages = $this->getMessages();
        $shippingCode = [];
        foreach ($this->shippingMethods as $key => $shippingMethod) {
            if ($this->request->isCalendar() && (isset($shippingMethod['calendar']) && !$shippingMethod['calendar'])) {
                unset($this->shippingMethods[$key]);
            } elseif (!$this->request->isCalendar()) {
                if (isset($shippingMethod['pickUpPoint']) && $shippingMethod['pickUpPoint']) {
                    $shippingCode['relayPoint'][] = $shippingMethod['code'];
                } elseif (!isset($shippingCode['shippingMethod'])
                    || !in_array($shippingMethod['code'], $shippingCode['shippingMethod'], true)
                ) {
                    $shippingCode['shippingMethod'][] = $shippingMethod['code'];
                }
            }
        }

        if (isset($shippingCode['shippingMethod'])
            && in_array(ShippingMethodInterface::METHOD_DPD, $shippingCode['shippingMethod'], true)
        ) {
            $shippingCode['shippingMethod'] = [ShippingMethodInterface::METHOD_DPD];
        }

        if (!$this->request->isCalendar()) {
            return $shippingCode;
        }

        return $this->formatArray(
            $messages,
            $this->formatSlotDuration($checkVariant)
        );
    }

    /**
     * @throws \Exception
     */
    private function formatSlotDuration(array $firstAvailableShippingDate): array
    {
        $deliverySlot = [];

        $dates = $this->getCalendarDay($firstAvailableShippingDate['nextAvailability']);
        foreach ($dates as $key => $date) {
            foreach ($date as $nb => $d) {
                if (isset($deliverySlot[$d]['times'])) {
                    if (0 !== $this->shippingMethods[$key]['priority'] && (isset($dates[0]) && !in_array($d, $dates[0]))) {
                        $deliverySlot[$d]['times'] = $this->checkTimePriority($this->shippingMethods[$key], $deliverySlot[$d], $nb);
                    }
                    continue;
                }
                /* If DPD isset OR current date not in array of DPD's dates, continue */
                if ((isset($this->shippingMethods[0]) && 0 !== $key) || (isset($dates[0]) && !in_array($d, $dates[0]))) {
                    continue;
                }
                if (isset($this->shippingMethods[$key]['transport_plan'])) {
                    $daySlot = $this->virTransportPlanService->getMakerTimeInformation($this->shippingMethods[$key], $d, $nb);
                } else {
                    $daySlot = $this->deliverySlotMaker->makeDeliverySlot($this->shippingMethods[$key], $nb, $d);
                }
                if (!empty($daySlot['times'])) {
                    $deliverySlot[$d] = $daySlot;
                }
            }
        }
        ksort($deliverySlot);

        return $deliverySlot;
    }

    /**
     * @throws \Exception
     */
    private function getCalendarDay(\DateTime $firstAvailableDate): array
    {
        $dates = [];
        foreach ($this->shippingMethods as $priority => &$shippingMethod) {
            if (str_contains((string) $shippingMethod['code'], ShippingMethodInterface::METHOD_VIR)) {
                $shippingMethod['transport_plan'] = $this->virTransportPlanService->getVirTransportPlan($this->request->getPostalCode(), $this->request->getCountryCode());
                $openingDay = $this->virTransportPlanService->getOpeningDays($shippingMethod['transport_plan']);

                // some postal code are not available for delivery (75, 91, 92, 93, 94, 95, 77, 78)
                if (empty($openingDay)) {
                    continue;
                }
            } else {
                $openingDay = $shippingMethod['shipping_opening_calendar_days'];
            }

            [$openingSchedule, $openingDays] = isset($shippingMethod['opening_schedule']) ? $this->getDatesFromArray($shippingMethod['opening_schedule']) : [[], []];
            [$closingSchedule, $closingDays] = isset($shippingMethod['closing_schedule']) ? $this->getDatesFromArray($shippingMethod['closing_schedule']) : [[], []];
            [$openingSlotSchedule, $openingCalendarSlot] = isset($shippingMethod['opening_slot_schedule']) ? $this->getDatesFromArray($shippingMethod['opening_slot_schedule']) : [[], []];
            [$warehouseClosingSchedule, $warehouseClosingDays] = isset($shippingMethod['warehouse_closing_schedule']) ? $this->getDatesFromArray($shippingMethod['warehouse_closing_schedule']) : [[], []];
            $firstAvailableDateClone = clone $firstAvailableDate->setTime(0, 0, 0);
            $day = $this->nextAvailableDateWarehouse($shippingMethod, $firstAvailableDateClone);
            $diff = $this->request->getDate()->diff($day)->days;
            if ($day <= $this->request->getDate()) {
                date_add($day, date_interval_create_from_date_string($diff . ' days'));
            }
            $dayPlace = 1;

            $i = 0;
            $j = false;

            while ($i <= $this->request->getLimit()
                && (null === $this->request->getDateLimit() || $day < $this->request->getDateLimit())
            ) {
                if (false === $j && $day > $this->request->getDate()) {
                    $nextDay = $day;
                    $j = true;
                } else {
                    $nextDay = date_add($day, date_interval_create_from_date_string($dayPlace . ' days'));
                }

                $closeDay = false;
                foreach ($closingDays as $exceptionalClosingDay) {
                    if ($nextDay > $exceptionalClosingDay['start'] && (int) $nextDay->format('N') === $exceptionalClosingDay['day']) {
                        $closeDay = true;
                        break;
                    }
                }

                $openDay = false;
                foreach ($openingCalendarSlot as $exceptionalOpeningDay) {
                    if ($nextDay > $exceptionalOpeningDay['start'] && (int) $nextDay->format('N') === $exceptionalOpeningDay['day']) {
                        $openDay = true;
                        break;
                    }
                }
                if ((in_array($nextDay->format('N'), $openingDay)                       /* Opening calendar days for the shippingMethod/ServiceRules */
                        || in_array($nextDay->format('Y-m-d'), $openingSchedule)            /* Opening Schedule days for the shippingMethod/ServiceRules */
                        || in_array($nextDay->format('Y-m-d'), $openingSlotSchedule)        /* Day of the opening Slot given */
                        || $openDay)                                                        /* Check if is an exceptional opening calendar day */
                    && !in_array($nextDay->format('Y-m-d'), $closingSchedule)           /* Closing Schedule for a given date */
                    && !in_array($nextDay->format('Y-m-d'), $warehouseClosingSchedule)  /* Warehouse closing Schedule */
                    && !$closeDay                                                       /* Case Day_Week Closed */
                ) {
                    $dates[$priority][] = $nextDay->format('Y-m-d');
                    ++$i;
                }
            }
        }

        return $dates;
    }

    /**
     * @throws \Exception
     */
    private function nextAvailableDateWarehouse($shippingMethod, \DateTime $firstAvailableDate): \DateTime
    {
        $openingDay = $shippingMethod['warehouse_opening_calendarDays'] ?? $shippingMethod['shipping_opening_calendar_days'];
        $openingTime = null !== $shippingMethod['warehouse_opening_calendarDays'] ? $shippingMethod['warehouse_opening_time'] : $shippingMethod['shipping_opening_time'];
        $closingTime = null !== $shippingMethod['warehouse_opening_calendarDays'] ? $shippingMethod['warehouse_closing_time'] : $shippingMethod['shipping_closing_time'];
        $cutOff = null !== $shippingMethod['warehouse_opening_calendarDays'] ? $shippingMethod['warehouse_cut_off'] : new \DateTime('11:30:00'); // need olvo entrepot
        $timeFirstAvailable = (new \DateTime('now'))->format('H:i');

        [$openingSchedule, $openingDays] = isset($shippingMethod['warehouse_opening_schedule']) ? $this->getDatesFromArray($shippingMethod['warehouse_opening_schedule']) : [[], []];
        [$closingSchedule, $closingDays] = isset($shippingMethod['warehouse_closing_schedule']) ? $this->getDatesFromArray($shippingMethod['warehouse_closing_schedule']) : [[], []];

        $currentDay = new \DateTime('now');
        $nextDay = (new \DateTime('now'))->add(new \DateInterval('P1D'));

        // We add the cutoff only if necessary (first available date is today or tomorrow)
        if ($currentDay->format('Y-m-d') === $firstAvailableDate->format('Y-m-d') || $nextDay->format('Y-m-d') === $firstAvailableDate->format('Y-m-d')) {
            $timeCutOff = 'PT' . $cutOff->format('H') . 'H' . $cutOff->format('i') . 'M';
            $firstAvailableDate->add(new \DateInterval($timeCutOff));
        }

        if ((!in_array($firstAvailableDate->format('N'), $openingDay)
                || $openingTime->format('H:i') >= $timeFirstAvailable
                || $timeFirstAvailable >= $closingTime->format('H:i')
                || ($timeFirstAvailable >= $cutOff->format('H:i') && ($currentDay->format('Y-m-d') === $firstAvailableDate->format('Y-m-d') || $nextDay->format('Y-m-d') === $firstAvailableDate->format('Y-m-d'))))
            && !in_array($firstAvailableDate->format('Y-m-d'), $openingSchedule)
        ) {
            $numberOfDay = null;
            foreach ($openingDay as $day) {
                if ($day > (int) $firstAvailableDate->format('N')) {
                    $numberOfDay = $day - $firstAvailableDate->format('N') . ' days';
                    break; // on veux le prochain jour de la semaine éligible
                } else {
                    $numberOfDay = ((7 - $firstAvailableDate->format('N')) + $openingDay[0]) . 'days'; // forcément le prochain premier jour calendaire
                }
            }

            if (null === $numberOfDay) {
                throw new \LogicException();
            }

            date_add($firstAvailableDate, date_interval_create_from_date_string($numberOfDay));
        }
        /*
         * Exceptional Closing day
         */
        while (in_array($firstAvailableDate->format('Y-m-d'), $closingSchedule)) {
            date_add($firstAvailableDate, date_interval_create_from_date_string('1 days'));
        }

        // first date can be different depend transport time
        if (null !== $shippingMethod['transport_time']) {
            $startDrivingDate = clone $firstAvailableDate;
            $transportTime = intval(24 * $shippingMethod['transport_time']);
            $firstAvailableDate->add(new \DateInterval('PT' . $transportTime . 'H'));
            $diff = $startDrivingDate->diff($firstAvailableDate)->days;
            $cutOffPassed = strtotime($currentDay->format('H:i:s')) > strtotime((string) $cutOff->format('H:i:s'));

            $shippingMethodOpeningDays = $shippingMethod['shipping_opening_calendar_days'];
            $i = (self::isShortSlot($shippingMethod) || $cutOffPassed) ? 1 : 0;
            for ($i; $i <= $diff; ++$i) {
                $startDrivingDate->add(new \DateInterval('P1D'));
                if (!in_array($startDrivingDate->format('N'), $shippingMethodOpeningDays)) {
                    $firstAvailableDate->add(new \DateInterval('P1D'));
                }
            }

            if (empty($shippingMethodOpeningDays)) {
                throw new \LogicException('No shipping method opening days defined');
            }

            $maxIterations = 7; // Maximum days in a week
            $iterations = 0;
            while (!in_array($firstAvailableDate->format('N'), $shippingMethodOpeningDays)) {
                $firstAvailableDate->add(new \DateInterval('P1D'));
                ++$iterations;

                if ($iterations >= $maxIterations) {
                    throw new \LogicException('Could not find valid shipping method opening day after checking entire week');
                }
            }
        }

        return $firstAvailableDate;
    }

    /**
     * @throws \Exception
     */
    private function checkTimePriority(array $otherMethodTimeCheck, array $priorityDeliverySlotDay, int $nb)
    {
        $otherMethodDeliverySlotDays = $this->deliverySlotMaker->makeDeliverySlot($otherMethodTimeCheck, $nb);
        $priorityTimes = null;

        foreach ($priorityDeliverySlotDay['times'] as $priority) {
            $priorityTimes[$priority['start_time']] = $priority['end_time'];
        }

        if (!is_array($priorityTimes)) {
            throw new \LogicException();
        }

        foreach ($otherMethodDeliverySlotDays['times'] as $otherMethod) {
            if (!array_key_exists($otherMethod['start_time'], $priorityTimes) && ($otherMethod['end_time'] <= array_key_first($priorityTimes) || $otherMethod['start_time'] >= end($priorityTimes))) {
                $priorityDeliverySlotDay['times'][] = $otherMethod;
            }
        }

        return $priorityDeliverySlotDay['times'];
    }

    private function getDatesFromArray($schedules): array
    {
        $dates = $calendarDays = [];
        foreach ($schedules as $schedule) {
            if (!empty($schedule['calendarDays'])) {
                foreach ($schedule['calendarDays'] as $day) {
                    $calendarDays[] = ['start' => $schedule['start'], 'day' => $day];
                }
            } else {
                $dates[] = $schedule['start']->format('Y-m-d');
            }
        }

        return [$dates, $calendarDays];
    }

    private function getMessages(): array
    {
        $messages = [];

        /**
         * @var ZipCode $zipcode
         */
        $zipcode = $this->zipCodeRepository->findOneBy(
            [
                'code' => $this->request->getPostalCode(),
                'country' => $this->request->getCountryCode(),
            ]
        );

        if (null !== $zipcode && null !== $zipcode->getZone()) {
            /**
             * @var Zone $zone
             */
            foreach ($zipcode->getZone() as $zone) {
                $messages = $this->zoneService->getZoneMessages($zone, $messages);
            }
        }

        return $messages;
    }

    private function formatArray($messages, $days)
    {
        $days['zipcode_alerts'] = $messages;

        return $days;
    }
}
