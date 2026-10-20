<?php

namespace App\Tests\Unit\Api\Odoo\Factory;

use App\Api\Odoo\Factory\AddressFactory;
use App\Api\Odoo\Model\BillingAddress;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Tests\TestCase;
use Sylius\Component\Addressing\Model\AddressInterface;
use Webmozart\Assert\InvalidArgumentException;

class AddressFactoryTest extends TestCase
{
    /** @var AddressFactory */
    private $factory;

    /** @var AddressInterface */
    private $address;

    /** @var CustomerInterface */
    private $customer;

    public function setUpFactory()
    {
        $this->factory = new AddressFactory();
    }

    private function setUpCreateNew()
    {
        $this->customer = new Customer();
        $address = new Address();
        $address->setFirstName('john');
        $address->setLastName('doe');

        $this->address = $address;

        $this->setUpFactory();
    }

    public function testCreateNew(): void
    {
        $this->setUpCreateNew();
        $address = $this->factory->createNew($this->address, $this->customer, BillingAddress::class);

        $this->assertInstanceOf(\App\Api\Odoo\Model\AddressInterface::class, $address);
        $this->assertInstanceOf(BillingAddress::class, $address);
    }

    public function testCreateNewFail(): void
    {
        $this->setUpCreateNew();

        $this->expectException(InvalidArgumentException::class);
        $this->factory->createNew($this->address, $this->customer, Address::class);
    }
}
