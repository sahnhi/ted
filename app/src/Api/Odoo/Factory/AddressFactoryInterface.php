<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\AddressInterface;
use App\Api\Odoo\Model\BillingAddress;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use Sylius\Component\Addressing\Model\AddressInterface as BaseAddressInterface;

interface AddressFactoryInterface
{
    public function createNew(BaseAddressInterface $address, CustomerInterface $customer, string $className = BillingAddress::class): AddressInterface;
}
