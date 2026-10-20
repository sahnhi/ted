<?php

namespace App\Tests\Unit\Api\Odoo\Model;

use App\Api\Odoo\Model\AbstractAddress;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Tests\TestCase;
use Sylius\Component\Addressing\Model\AddressInterface;

class AbstractAddressTest extends TestCase
{
    /** @var AbstractAddress */
    private $address;

    /**
     * @throws \ReflectionException
     */
    public function setUpModel(AddressInterface $address, CustomerInterface $customer): void
    {
        $this->address = $this->getMockForAbstractClass(AbstractAddress::class, [$address, $customer]);
    }

    public function testConstruct(): void
    {
        $address = new Address();
        $address->setLastName('lastName');
        $address->setFirstName('firstName');
        $address->setPhoneNumber('phoneNumber');
        $address->setStreet('street');
        $address->setCity('city');
        $address->setCountryCode('countryCode');
        $address->setPostcode('postCode');

        $customer = new Customer();
        $customer->setEmail('email');

        $this->setUpModel($address, $customer);
        $this->assertEquals($address->getLastName(), $this->address->lastname);
        $this->assertEquals($address->getFirstName(), $this->address->firstname);
        $this->assertEquals($address->getPhoneNumber(), $this->address->phone);
        $this->assertEquals($address->getStreet(), $this->address->street);
        $this->assertEquals($address->getCity(), $this->address->city);
        $this->assertEquals($address->getCountryCode(), $this->address->country);
        $this->assertEquals($address->getPostcode(), $this->address->zip);
        $this->assertEquals($customer->getEmail(), $this->address->mail);
    }
}
