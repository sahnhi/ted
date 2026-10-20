<?php

namespace App\ERP\Application\Query;

use App\ERP\Infrastructure\Shared\Bus\Query\QueryInterface;

interface QueryBusInterface
{
    public function handle(QueryInterface $query): mixed;
}
