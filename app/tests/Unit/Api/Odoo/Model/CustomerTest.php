<?php

namespace App\Tests\Unit\Api\Odoo\Model;

use App\Api\Odoo\Model\Customer;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Customer as BaseCustomer;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use App\Tests\TestCase;
use Sylius\Component\Addressing\Model\AddressInterface;
use Sylius\Component\Core\Model\TaxRate;
use Sylius\Component\Core\Model\TaxRateInterface;

class CustomerTest extends TestCase
{
    /** @var Customer */
    private $customer;

    public function setUpModel(CustomerInterface $customer, AddressInterface $address, ?TaxRateInterface $taxRate): void
    {
        $this->customer = new Customer($customer, $address, $taxRate);
    }

    /**
     * @return array
     */
    private function getParams()
    {
        $customer = new BaseCustomer();
        $customer->setEmail('email');
        $customer->setLastName('lastName');
        $customer->setFirstName('firstName');
        $customer->setPhoneNumber('phoneNumber');

        $address = new Address();
        $address->setCompany('company');

        $taxRate = new TaxRate();
        $taxRate->setCode('code');

        return [$customer, $address, $taxRate];
    }

    public function testConstruct(): void
    {
        [$customer, $address, $taxRate] = $this->getParams();

        $this->setUpModel($customer, $address, $taxRate);
        $this->assertEquals($customer->getLastName(), $this->customer->lastname);
        $this->assertEquals($customer->getFirstName(), $this->customer->firstname);
        $this->assertEquals($customer->getEmail(), $this->customer->mail);
        $this->assertEquals($customer->getPhoneNumber(), $this->customer->phone);
        $this->assertEquals($address->getCompany(), $this->customer->commercialname);
        $this->assertEquals($taxRate->getCode(), $this->customer->vat);
    }

    public function testConstructNoTaxRate(): void
    {
        [$customer, $address, $taxRate] = $this->getParams();
        $taxRate = null;

        $this->setUpModel($customer, $address, $taxRate);
        $this->assertEmpty($this->customer->vat);
    }
}
