<?php

namespace App\Api\Odoo\Model;

use App\Doctrine\Entity\Sylius\CustomerInterface;
use Sylius\Component\Addressing\Model\AddressInterface as BaseAddressInterface;

interface AddressInterface extends ObjectMessageOdooInterface
{
    public function __construct(BaseAddressInterface $address, CustomerInterface $customer);
}
