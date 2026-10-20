<?php

namespace App\Validator\Constraints;

use Sylius\Bundle\InventoryBundle\Validator\Constraints\InStock;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class InStockValidator extends ConstraintValidator
{
    private readonly PropertyAccessor $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function validate($value, Constraint $constraint): void
    {
        /* @var InStock $constraint */
        Assert::isInstanceOf($constraint, InStock::class);

        $stockable = $this->accessor->getValue($value, $constraint->stockablePath);
        if (null === $stockable) {
            return;
        }

        $quantity = $this->accessor->getValue($value, $constraint->quantityPath);
        if (null === $quantity) {
            return;
        }
    }
}
