<?php

namespace App\Validator;

use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ProductVariantExistsValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        $product = $this->productVariantRepository->findOneBy(['code' => $value]);

        if (null === $product) {
            $this->context->addViolation($constraint->message);
        }
    }
}
