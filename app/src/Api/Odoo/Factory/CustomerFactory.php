<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\Customer;
use App\Api\Odoo\Model\CustomerInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Provider\TaxRateProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;

class CustomerFactory implements CustomerFactoryInterface
{
    public function __construct(
        private readonly TaxRateProviderInterface $taxRateProvider
    ) {
    }

    public function createNew(OrderInterface $syliusOrder): CustomerInterface
    {
        /** @var Order $syliusOrder */
        $customer = $syliusOrder->getCustomer();
        $address = $syliusOrder->getBillingAddress() ?? $syliusOrder->getShippingAddress();
        $taxRate = $this->taxRateProvider->provideTaxRateFromZone($syliusOrder);

        return new Customer($customer, $address, $taxRate);
    }
}
