<?php

namespace App\Tests\Unit\Api\Odoo\Factory;

use App\Api\Odoo\Factory\CustomerFactory;
use App\Api\Odoo\Model\CustomerInterface;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Provider\TaxRateProviderInterface;
use App\Tests\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\TaxRate;

class CustomerFactoryTest extends TestCase
{
    private CustomerFactory $factory;
    private TaxRateProviderInterface $taxRateProvider;
    private OrderInterface $order;

    public function setUp(): void
    {
        parent::setUp();

        $this->taxRateProvider = $this->createMock(TaxRateProviderInterface::class);
    }

    public function setUpFactory()
    {
        $this->factory = new CustomerFactory(
            $this->taxRateProvider
        );
    }

    private function setUpCreateNew()
    {
        $customer = new Customer();
        $customer->setFirstName('john');
        $customer->setLastName('doe');
        $address = new Address();
        $address->setFirstName('john');
        $address->setLastName('doe');

        $this->order = new Order();
        $this->order->setCustomer($customer);
        $this->order->setShippingAddress($address);

        $this->taxRateProvider
            ->method('provideTaxRateFromZone')
            ->willReturn(new TaxRate());

        $this->setUpFactory();
    }

    public function testCreateNew(): void
    {
        $this->setUpCreateNew();
        $customer = $this->factory->createNew($this->order);

        $this->assertInstanceOf(CustomerInterface::class, $customer);
    }

    public function testCreateNewWithBillingAddress(): void
    {
        $this->setUpCreateNew();
        $billingAddress = new Address();
        $billingAddress->setFirstName('john');
        $billingAddress->setLastName('doe');
        $this->order->setBillingAddress($billingAddress);

        $customer = $this->factory->createNew($this->order);

        $this->assertInstanceOf(CustomerInterface::class, $customer);
    }
}
