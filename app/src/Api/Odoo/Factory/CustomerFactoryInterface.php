<?php

namespace App\Api\Odoo\Factory;

use App\Api\Odoo\Model\CustomerInterface;
use App\Provider\TaxRateProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface CustomerFactoryInterface
{
    public function __construct(TaxRateProviderInterface $taxRateProvider);

    public function createNew(OrderInterface $syliusOrder): CustomerInterface;
}
