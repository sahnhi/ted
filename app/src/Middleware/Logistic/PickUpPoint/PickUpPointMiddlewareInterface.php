<?php

declare(strict_types=1);

namespace App\Middleware\Logistic\PickUpPoint;

use App\Model\Logistic\PickUp\SearchableAddress;

interface PickUpPointMiddlewareInterface
{
    public function fetchPoints(SearchableAddress $searchableAddress);

    public function getCarrier(): string;

    public function isEnabled(): bool;
}
