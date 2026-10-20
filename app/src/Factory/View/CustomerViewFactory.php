<?php

namespace App\Factory\View;

use App\View\CustomerTransmissionView;
use App\View\CustomerView;
use App\ViewRepository\CustomerViewRepositoryInterface;
use Sylius\Component\Core\Model\CustomerInterface;

final class CustomerViewFactory implements CustomerViewFactoryInterface
{
    public function __construct(
        private readonly CustomerViewRepositoryInterface $customerViewRepository
    ) {
    }

    public function create(CustomerInterface $customer, $tokenValue = null): CustomerView
    {
        $customerView = new CustomerView();
        $customerView->id = $customer->getId();
        $customerView->email = $customer->getEmail();
        $customerView->phoneNumber = $customer->getPhoneNumber();
        $customerView->new = $this->customerViewRepository->isNewCustomer($customer->getId(), $tokenValue);

        return $customerView;
    }

    public function createTransmission(CustomerInterface $customer): CustomerTransmissionView
    {
        $customerTransmissionView = new CustomerTransmissionView();
        $customerTransmissionView->email = $customer->getEmail();

        return $customerTransmissionView;
    }
}
