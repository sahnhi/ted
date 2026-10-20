<?php

declare(strict_types=1);

namespace App\Calendar\ViewModels\Factory;

use App\Calendar\ViewModels\CalendarSlots;

final class CalendarSlotsFactory
{
    public function __construct(
        private readonly CalendarSlotFactory $calendarSlotFactory
    ) {
    }

    public function create(array $calendarSlots): CalendarSlots
    {
        $calendarSlotsView = new CalendarSlots();

        foreach ($calendarSlots as $atDate => $calendarSlot) {
            if (!$calendarSlot || 'zipcode_alerts' === $atDate) {
                continue;
            }

            $calendarSlotsView->addSlots(
                $this->calendarSlotFactory->create($calendarSlot, $atDate)
            );
        }

        return $calendarSlotsView;
    }
}
