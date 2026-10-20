<?php

namespace App\Validator;

use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ConfigurableProductValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        $product = $this->productRepository->findOneByCode($value);

        if (null === $product || $product->isConfigurable()) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }
}
