<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class CalendarAvailabilityProducts extends Constraint
{
    /**
     * @var string
     */
    public $notEmptyMessage = 'app.shop_api.calendar_availability.products.not_empty';

    public function validatedBy()
    {
        return 'app_shop_api_calendar_availability_products_validator';
    }
}
