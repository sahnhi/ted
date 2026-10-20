<?php

namespace App\Provider;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class CustomerProvider implements CustomerProviderInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly FactoryInterface $customerFactory
    ) {
    }

    public function provide(string $email): CustomerInterface
    {
        $customer = $this->customerRepository->findOneBy(['email' => $email]);

        if (null === $customer) {
            /** @var CustomerInterface $customer */
            $customer = $this->customerFactory->createNew();
            $customer->setEmail($email);
        }

        return $customer;
    }

    /**
     * This method should not be called since we override the original CustomerProvider from AdminOrderCreationPlugin.
     */
    public function provideExistingCustomer(string $id): CustomerInterface
    {
        throw new \BadMethodCallException('This method is disabled');
    }

    public function provideNewCustomer(string $email): CustomerInterface
    {
        $customer = $this->customerRepository->findOneBy(['email' => $email]);

        if (null === $customer) {
            /** @var CustomerInterface $customer */
            $customer = $this->customerFactory->createNew();
            $customer->setEmail($email);
            $this->customerRepository->add($customer);
        }

        return $customer;
    }
}
