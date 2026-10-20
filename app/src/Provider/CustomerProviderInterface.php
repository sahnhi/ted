<?php

namespace App\Provider;

use Sylius\AdminOrderCreationPlugin\Provider\CustomerProviderInterface as BaseCustomerProviderInterface;
use Sylius\Component\Core\Model\CustomerInterface;

interface CustomerProviderInterface extends BaseCustomerProviderInterface
{
    public function provide(string $email): CustomerInterface;

    public function provideExistingCustomer(string $id): CustomerInterface;

    public function provideNewCustomer(string $email): CustomerInterface;
}
