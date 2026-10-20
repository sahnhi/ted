<?php

namespace App\Services\Date;

class DateHelper
{
    public function __construct(
        public ExceptionalScheduleHelperInterface $exceptionalScheduleHelper,
        public ShippingMethodDateHelperInterface $shippingMethodDateHelper,
        public WarehouseDateHelperInterface $warehouseDateHelper
    ) {
    }
}
