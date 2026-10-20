<?php

namespace App\Validator\Constraints;

use Sylius\Bundle\CoreBundle\Validator\Constraints\CartItemAvailability;
use Sylius\Bundle\OrderBundle\Controller\AddToCartCommandInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class CartItemAvailabilityValidator extends ConstraintValidator
{
    /**
     * @param AddToCartCommandInterface $value
     */
    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($value, AddToCartCommandInterface::class);
        Assert::isInstanceOf($constraint, CartItemAvailability::class);
    }
}
