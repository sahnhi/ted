<?php

namespace App\Factory\View;

use App\View\CustomerTransmissionView;
use App\View\CustomerView;
use Sylius\Component\Core\Model\CustomerInterface;

interface CustomerViewFactoryInterface
{
    public function create(CustomerInterface $customer, $tokenValue): CustomerView;

    public function createTransmission(CustomerInterface $customer): CustomerTransmissionView;
}
