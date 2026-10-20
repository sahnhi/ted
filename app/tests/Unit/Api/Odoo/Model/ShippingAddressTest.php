<?php

namespace App\Tests\Unit\Api\Odoo\Model;

use App\Api\Odoo\Model\ShippingAddress;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Tests\TestCase;
use Sylius\Component\Addressing\Model\AddressInterface;

class ShippingAddressTest extends TestCase
{
    /** @var ShippingAddress */
    private $shippingAddress;

    public function setUpModel(AddressInterface $address, CustomerInterface $customer): void
    {
        $this->shippingAddress = new ShippingAddress($address, $customer);
    }

    public function testConstruct(): void
    {
        $address = new Address();
        $address->setDetails('details');
        $address->setFirstName('john');
        $address->setLastName('doe');

        $customer = new Customer();

        $this->setUpModel($address, $customer);
        $this->assertEquals($address->getDetails(), $this->shippingAddress->notes);
    }
}
