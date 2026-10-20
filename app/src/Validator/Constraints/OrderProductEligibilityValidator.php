<?php

namespace App\Validator\Constraints;

use Sylius\Bundle\CoreBundle\Validator\Constraints\OrderProductEligibility;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class OrderProductEligibilityValidator extends ConstraintValidator
{
    /**
     * @throws \InvalidArgumentException
     */
    public function validate($order, Constraint $constraint): void
    {
        /* @var OrderInterface $order */
        Assert::isInstanceOf($order, OrderInterface::class);

        /* @var OrderProductEligibility $constraint */
        Assert::isInstanceOf($constraint, OrderProductEligibility::class);

        /** @var OrderItemInterface[] $orderItems */
        $orderItems = $order->getItems();

        /* BEGIN : custom from original file : remove check product is enabled validation */
        /*foreach ($orderItems as $orderItem) {
            if (!$orderItem->getProduct()->isEnabled()) {
                $this->context->addViolation(
                    $constraint->message,
                    ['%productName%' => $orderItem->getProduct()->getName()]
                );
            }
        }*/
        /* ENDED : custom from original file : remove check product is enabled validation */
    }
}
