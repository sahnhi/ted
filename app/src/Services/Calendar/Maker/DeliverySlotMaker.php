<?php

namespace App\Services\Calendar\Maker;

use App\Services\Calendar\CalendarApi;
use DateTime;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class DeliverySlotMaker implements DeliverySlotMakerInterface
{
    public const MIN_SLOT_DURATION_DAILY_DELIVERY = 310;

    /** @var \DateTime */
    private $date;
    /** @var array */
    private $times;
    /** @var bool */
    private $dailyFlag = false;

    private $dailyDelivery = false;
    /** @var RepositoryInterface ; */
    private $shippingMethodRepository;

    /**
     * DeliverySlotMaker constructor.
     */
    public function __construct(
        private readonly TimeMaker $timeMaker
    ) {
    }

    /**
     * @throws \Exception
     */
    public function makeDeliverySlot($shippingMethod, $nbrOfDay, $day = null): array
    {
        $times = [
            'times' => [],
            'dailyFlag' => $this->dailyFlag,
            'dailyDelivery' => $this->dailyDelivery,
        ];

        /** same format comparison DateTime to now && reusable openingTime for each date */
        $timeSlot = $shippingMethod['delivery_slot']->format('H:i');
        $openingTime = new \DateTime($shippingMethod['shipping_opening_time']->format('H:i'));
        $closingTime = new \DateTime($shippingMethod['shipping_closing_time']->format('H:i'));
        $timeSlotInterval = new \DateInterval('PT' . $this->convertTimeToDecimal($timeSlot) . 'M');
        $date = new \DateTime($day);
        $now = new \DateTime();
        $openingSchedule = isset($shippingMethod['opening_slot_schedule']) ? $this->getSlotFromSchedule($shippingMethod['opening_slot_schedule']) : [];
        $closingSchedule = isset($shippingMethod['closing_slot_schedule']) ? $this->getClosingSlotFromSchedule($shippingMethod['closing_slot_schedule']) : [];

        foreach ($openingSchedule as $schedule) {
            if (!empty($schedule['calendarDays']) && in_array($date->format('N'), $schedule['calendarDays'])) {
                $openingTime = new \DateTime($date->format('Y-m-d') . ' ' . $schedule['start']->format('H:i'));
                $closingTime = new \DateTime($date->format('Y-m-d') . ' ' . $schedule['end']->format('H:i'));
            } elseif ($day === $schedule['start']->format('Y-m-d')) {
                $openingTime = $schedule['start'];
                $closingTime = new \DateTime($schedule['start']->format('Y-m-d') . ' ' . $schedule['end']->format('H:i'));
            }
        }
        if (0 === $nbrOfDay && !$this->isNextMonth($date, $now, $shippingMethod) && 'VIR' !== $shippingMethod['code']) {
            $cutOff = null !== $shippingMethod['warehouse_opening_calendarDays'] ?
                clone $shippingMethod['warehouse_cut_off'] :
                new \DateTime('11:30:00'); // need olvo entrepot
            $cutOffPassed = strtotime($now->format('H:i:s')) > strtotime((string) $cutOff->format('H:i:s'));

            $isSameDay = $now->format('d-m-Y') === $date->format('d-m-Y');
            $daysDiff = $isSameDay ? 0 : $date->diff($now)->days;
            $transportTime = intval(24 * $shippingMethod['transport_time']);

            $cutOff->setDate($date->format('Y'), $date->format('m'), $date->format('d'));
            $cutOff->add(new \DateInterval('PT' . $transportTime . 'H'));
            $cutOffTooLate = strtotime((string) $cutOff->format('Y-m-d H:i:s')) > strtotime($date->format('Y-m-d') . ' ' . $shippingMethod['shipping_closing_time']->format('H:i:s'));

            if ($cutOffPassed && $daysDiff > 1 && !$cutOffTooLate && !CalendarApi::isShortSlot($shippingMethod)) {
                unset($cutOff);
            }
        }

        $startTime = new \DateTime($openingTime->format('Y-m-d H:i'));
        $endTime = new \DateTime($openingTime->format('Y-m-d  H:i'));
        $endTime->add($timeSlotInterval);

        if ($this->convertTimeToDecimal($timeSlot) > self::MIN_SLOT_DURATION_DAILY_DELIVERY) {
            $times['dailyDelivery'] = true;
        }
        if ($startTime->format('H:i') !== $openingTime->format('H:i') && !$times['dailyDelivery']) {
            $times['dailyFlag'] = true;
        }

        while (intval($endTime->format('H')) <= intval($closingTime->format('H')) && $endTime->format('N') === $closingTime->format('N')) {
            if (isset($cutOff) && $startTime->format('H:i') < $cutOff->format('H:i')
                || (!empty($closingSchedule) && array_key_exists($day, $closingSchedule)  /* Check closing timeSlot schedule */
                    && $startTime->format('H:i') >= $closingSchedule[$day]['start']
                    && $startTime->format('H:i') < $closingSchedule[$day]['end'])
            ) {
                $startTime->add($timeSlotInterval);
                $endTime->add($timeSlotInterval);
                continue;
            }
            $makeTime = [
                'place' => $shippingMethod['id_balou'],
                'provider_name' => $shippingMethod['code'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'dailyDelivery' => $times['dailyDelivery'],
                'shipping_message' => $shippingMethod['shipping_message'],
            ];
            $times['times'][] = $this->timeMaker->makeTime($makeTime);
            $startTime->add($timeSlotInterval);
            $endTime->add($timeSlotInterval);
        }
        if (!empty($times['times']) && end($times['times'])['end_time'] < $closingTime->format('H:i') && !$times['dailyDelivery']) {
            $times['dailyFlag'] = true;
        }

        return $times;
    }

    /**
     * @return float|int|mixed
     */
    public function convertTimeToDecimal($time)
    {
        $timeArr = explode(':', (string) $time);

        return ($timeArr[0] * 60) + $timeArr[1];
    }

    public function getSlotFromSchedule(array $schedules): array
    {
        $slots = $calendarDays = [];
        foreach ($schedules as $schedule) {
            $calendarDays = [];
            if (!empty($schedule['calendarDays'])) {
                foreach ($schedule['calendarDays'] as $day) {
                    $calendarDays[] = $day;
                }
            }
            $slots[] = [
                'start' => $schedule['start'],
                'end' => $schedule['end'],
                'calendarDays' => $calendarDays,
            ];
        }

        return $slots;
    }

    public function getClosingSlotFromSchedule(array $schedules): array
    {
        $slots = [];
        foreach ($schedules as $schedule) {
            $slots[$schedule['start']->format('Y-m-d')] = [
                'start' => $schedule['start']->format('H:i'),
                'end' => $schedule['end']->format('H:i'),
            ];
        }

        return $slots;
    }

    /**
     * @throws \Exception
     */
    private function isNextMonth(\DateTime $date, \DateTime $now, array $shippingMethod): bool
    {
        if ($date->format('m') === $now->format('m') || CalendarApi::isShortSlot($shippingMethod)) {
            return false;
        }

        $transportTime = intval(24 * $shippingMethod['transport_time']);

        $dateInterval = new \DateInterval('P1D');
        $start = clone $now;
        $start->add(new \DateInterval('PT' . $transportTime . 'H'));
        $monthChanged = false;

        while ($start < $date) {
            // If it's opened before, then it means it's a manual date-change
            if (!empty($shippingMethod['shipping_opening_calendar_days']) && in_array($start->format('N'), $shippingMethod['shipping_opening_calendar_days'])) {
                $monthChanged = true;
            }

            $start->add($dateInterval);
        }

        return $monthChanged;
    }
}
