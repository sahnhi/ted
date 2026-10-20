<?php

namespace App\Validator;

use App\Validator\Constraints\CalendarAvailabilityProducts;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class CalendarAvailabilityProductsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CalendarAvailabilityProducts) {
            throw new UnexpectedTypeException($constraint, CalendarAvailabilityProducts::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_array($value) && !($value instanceof \Traversable && $value instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($value, 'array or Traversable and ArrayAccess');
        }

        if (0 === count($value)) {
            $this
                ->context
                ->buildViolation($constraint->notEmptyMessage)
                ->addViolation();
        }

        foreach ($value as $index => $element) {
            $this
                ->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath('[' . $index . ']')
                ->validate($element, [
                    new Collection([
                        'fields' => [
                            'code' => [
                                new NotBlank(),
                            ],
                            'quantity' => [
                                new GreaterThan([
                                    'value' => 0,
                                ]),
                            ],
                        ],
                    ]),
                ]);
        }
    }
}
