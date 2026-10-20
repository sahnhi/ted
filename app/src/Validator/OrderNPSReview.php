<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class OrderNPSReview extends Constraint
{
    public string $message = 'The order nps review is already set.';

    public function __construct(?array $groups = null)
    {
        parent::__construct($groups);
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return OrderNPSReviewValidator::class;
    }
}
