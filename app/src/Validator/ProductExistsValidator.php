<?php

namespace App\Validator;

use App\Doctrine\Repository\Sylius\ProductRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ProductExistsValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (null === $value || null === $this->productRepository->findOneByCode($value)) {
            $this->context->addViolation($constraint->message);
        }
    }
}
