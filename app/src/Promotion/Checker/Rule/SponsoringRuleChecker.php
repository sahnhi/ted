<?php

namespace App\Promotion\Checker\Rule;

use App\Doctrine\Entity\Sylius\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

final class SponsoringRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'sponsoring';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CustomerRepositoryInterface $customerRepository
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

        /** @var CustomerInterface $customer */
        $customer = $subject->getCustomer();
        if (null === $customer) {
            return true;
        }

        return $this->customerRepository->countNbFilleul($customer) >= $configuration['nth'];
    }
}
