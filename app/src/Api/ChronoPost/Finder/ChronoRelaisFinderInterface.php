<?php

namespace App\Api\ChronoPost\Finder;

use App\Api\ChronoPost\Model\ChronoRelaisList;
use App\Api\ChronoPost\Query\ChronoRelaisQuery;

interface ChronoRelaisFinderInterface
{
    public function findNearBy(ChronoRelaisQuery $chronoRelaisQuery): ChronoRelaisList;
}
