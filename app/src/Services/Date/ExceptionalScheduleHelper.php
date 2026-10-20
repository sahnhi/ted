<?php

namespace App\Services\Date;

use App\Doctrine\Entity\ExceptionalSchedule;

class ExceptionalScheduleHelper implements ExceptionalScheduleHelperInterface
{
    private bool $default = true;

    public function isOpen(\DateTime $date, ExceptionalSchedule $schedule, $default = true): bool
    {
        $this->default = $default;

        $type = $schedule->getType();
        switch ($type) {
            case 'opening':
                return $this->dayCase($date, $schedule, true);
            case 'closing':
                return $this->dayCase($date, $schedule, false);
            case 'opening_slot':
                return $this->slotCase($date, $schedule, true);
            case 'closing_slot':
                return $this->slotCase($date, $schedule, false);
            default:
                throw new \InvalidArgumentException();
        }
    }

    private function slotCase(\DateTime $date, ExceptionalSchedule $schedule, bool $opening): bool
    {
        if (!empty($schedule->getCalendarDays())) {
            return $this->weekDayCase($date, $schedule, $opening, true);
        }

        return $this->checkSlot($date, $schedule) ? $opening : $this->default;
    }

    private function dayCase(\DateTime $date, ExceptionalSchedule $schedule, bool $opening): bool
    {
        if (!empty($schedule->getCalendarDays())) {
            return $this->weekDayCase($date, $schedule, $opening, false);
        }

        return $date->format('Y-m-d') === $schedule->getStart()->format('Y-m-d') ? $opening : $this->default;
    }

    private function weekDayCase(\DateTime $date, ExceptionalSchedule $schedule, bool $opening, bool $isSlot): bool
    {
        if ($isSlot) {
            return in_array($date->format('N'), $schedule->getCalendarDays()) && $this->checkSlot($date, $schedule) ? $opening : $this->default;
        }

        return in_array($date->format('N'), $schedule->getCalendarDays()) ? $opening : $this->default;
    }

    private function checkSlot(\DateTime $date, ExceptionalSchedule $schedule): bool
    {
        return $date >= $schedule->getStart() && $date <= $schedule->getEnd();
    }
}
