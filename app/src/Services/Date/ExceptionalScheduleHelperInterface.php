<?php

namespace App\Services\Date;

use App\Doctrine\Entity\ExceptionalSchedule;

interface ExceptionalScheduleHelperInterface
{
    /**
     * @param bool $default
     */
    public function isOpen(\DateTime $date, ExceptionalSchedule $schedule, $default = true): bool;
}
