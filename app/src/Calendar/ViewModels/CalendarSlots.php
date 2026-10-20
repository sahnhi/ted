<?php

declare(strict_types=1);

namespace App\Calendar\ViewModels;

final class CalendarSlots
{
    private array $calendarSlots = [];

    public function getCalendarSlots(): array
    {
        return $this->calendarSlots;
    }

    public function addSlots(CalendarSlot $calendarSlot): self
    {
        $this->calendarSlots[] = $calendarSlot;

        return $this;
    }

    public function getCalendarSlot(int $key): CalendarSlot
    {
        return $this->calendarSlots[$key];
    }

    public function removeCalendarSlot(int $key): void
    {
        unset($this->calendarSlots[$key]);
    }
}
