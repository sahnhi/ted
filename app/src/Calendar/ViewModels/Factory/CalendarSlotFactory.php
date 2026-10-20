<?php

declare(strict_types=1);

namespace App\Calendar\ViewModels\Factory;

use App\Calendar\ViewModels\CalendarSlot;
use App\Calendar\ViewModels\DeliveryTime;
use App\Calendar\ViewModels\DeliveryTimes;

final class CalendarSlotFactory
{
    public function create(array $calendarSlot, string $atDate): CalendarSlot
    {
        $timesView = new DeliveryTimes();
        foreach ($calendarSlot['times'] as $time) {
            $timesView->addTime(
                new DeliveryTime(
                    $time['place'],
                    $time['providerName'],
                    $time['dailyDelivery'],
                    $atDate . ' ' . $time['start_time'],
                    $atDate . ' ' . $time['end_time'],
                    $time['shipping_message'],
                )
            );
        }

        return new CalendarSlot(
            $timesView,
            $calendarSlot['dailyFlag'],
            $calendarSlot['dailyDelivery']
        );
    }
}
