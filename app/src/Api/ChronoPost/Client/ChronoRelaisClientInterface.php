<?php

namespace App\Api\ChronoPost\Client;

use App\Api\ChronoPost\Query\ChronoRelaisQuery;

interface ChronoRelaisClientInterface
{
    public function findNearBy(ChronoRelaisQuery $chronoRelaisQuery): array;
}
