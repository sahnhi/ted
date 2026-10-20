<?php

namespace App\Services\Review\Seek;

use App\Utils\DateRange;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

interface SeekerInterface
{
    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function seekReviews(DateRange $dateRange, int $total): void;
}
