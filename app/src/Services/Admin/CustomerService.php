<?php

namespace App\Services\Admin;

use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Repository\Sylius\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\Factory;
use Webmozart\Assert\Assert;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly Factory $customerFactory,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function getCustomerByEmailOrCreate(string $email): Customer
    {
        Assert::email($email);

        $customer = $this->customerRepository->findOneByEmail($email);
        if (!$customer instanceof Customer) {
            $customer = $this->customerFactory->createNew();
            $customer->setEmail($email);

            $this->entityManager->persist($customer);
            $this->entityManager->flush();
        }

        return $customer;
    }
}
