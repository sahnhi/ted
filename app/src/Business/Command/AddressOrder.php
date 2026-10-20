<?php

namespace App\Business\Command;

use App\Model\Address;
use App\Model\Customer;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class AddressOrder
{
    public function __construct(
        private readonly string $orderToken,
        private readonly Address $address,
        private readonly Address $billingAddress,
        private readonly Customer $customer,
        private readonly OrderInterface $order
    ) {
        Assert::string($orderToken);
    }

    public function orderToken(): string
    {
        return $this->orderToken;
    }

    public function customer(): Customer
    {
        return $this->customer;
    }

    public function shippingAddress(): Address
    {
        return $this->address;
    }

    public function billingAddress(): Address
    {
        return $this->billingAddress;
    }

    public function order(): OrderInterface
    {
        return $this->order;
    }
}
