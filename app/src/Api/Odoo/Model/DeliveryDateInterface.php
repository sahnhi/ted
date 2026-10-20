<?php

namespace App\Api\Odoo\Model;

interface DeliveryDateInterface extends ObjectMessageOdooInterface
{
    public function __construct(\DateTimeInterface $startDate, \DateTimeInterface $endDate);
}
