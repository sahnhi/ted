<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class CustomerEmail extends Constraint
{
    public $message = 'sylius.customer.email.invalid';

    public function validatedBy()
    {
        return 'app_invalid_customer_email';
    }
}
