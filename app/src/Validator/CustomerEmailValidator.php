<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CustomerEmailValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($this->validEmail($value)) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }

    public function validEmail(string $str): bool
    {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? false : true;
    }
}
