<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\AddressInterface;
use App\Api\Odoo\Model\ShippingAddress;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use Sylius\Component\Addressing\Model\AddressInterface as BaseAddressInterface;
use Webmozart\Assert\Assert;

class AddressFactory implements AddressFactoryInterface
{
    public function createNew(BaseAddressInterface $address, CustomerInterface $customer, string $className = ShippingAddress::class): AddressInterface
    {
        $class = new $className($address, $customer);
        Assert::isInstanceOf($class, AddressInterface::class);

        return $class;
    }
}
