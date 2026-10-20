<?php

namespace App\Validator;

use App\Doctrine\Entity\Sylius\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OrderNPSReviewValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof OrderNPSReview) {
            throw new UnexpectedTypeException($constraint, OrderNPSReview::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof Order);

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $originalData = $unitOfWork->getOriginalEntityData($value);
        if (null !== $originalData['NPS']) {
            $this->context->buildViolation($constraint->message)->atPath('NPS')->addViolation();
        }
    }
}
