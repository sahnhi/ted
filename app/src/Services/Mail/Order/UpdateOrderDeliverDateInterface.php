<?php

namespace App\Services\Mail\Order;

interface UpdateOrderDeliverDateInterface
{
    public function notifyNewDate(string $commandNumber, \DateTimeInterface $startDate, \DateTimeInterface $endDate): void;
}
