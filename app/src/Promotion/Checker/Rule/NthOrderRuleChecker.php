<?php

namespace App\Promotion\Checker\Rule;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

final class NthOrderRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'nth_order';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnsupportedTypeException($subject, OrderInterface::class);
        }

        if (!isset($configuration['nth']) || !is_int($configuration['nth'])) {
            return false;
        }

        $customer = $subject->getCustomer();
        if (null === $customer) {
            return false;
        }

        // eligible if it is first order of guest and the promotion is on first order
        if (null === $customer->getId()) {
            return 1 === $configuration['nth'];
        }

        // custom code
        return $this->orderRepository->countPaidByCustomer($customer) === ($configuration['nth'] - 1);
    }
}
