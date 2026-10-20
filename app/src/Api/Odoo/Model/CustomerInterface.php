<?php

namespace App\Api\Odoo\Model;

use App\Doctrine\Entity\Sylius\CustomerInterface as BaseCustomerInterface;
use Sylius\Component\Addressing\Model\AddressInterface;
use Sylius\Component\Core\Model\TaxRateInterface;

interface CustomerInterface extends ObjectMessageOdooInterface
{
    public function __construct(BaseCustomerInterface $customer, AddressInterface $address, ?TaxRateInterface $taxRate);
}
