<?php

namespace App\Services\Drip\Batch;

use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Services\Drip\DripAPI;
use Doctrine\Common\Collections\ArrayCollection;

final class DripBatchFulfilled
{
    private readonly ArrayCollection $orders;
    private readonly ArrayCollection $customers;

    public function __construct(
        private readonly DripAPI $dripApi
    ) {
        $this->orders = new ArrayCollection();
        $this->customers = new ArrayCollection();
    }

    public function sendBatchOrder()
    {
        $this->dripApi->sendBatchOrders($this->getOrders()->toArray());
    }

    public function sendBatchCustomer()
    {
        $this->dripApi->sendBatchCustomers($this->getCustomers()->toArray());
    }

    public function addOrder(Order $order)
    {
        $this->orders->add($order);
    }

    public function addCustomer(Customer $customer)
    {
        $this->customers->add($customer);
    }

    public function getOrders(): ArrayCollection
    {
        return $this->orders;
    }

    public function getCustomers(): ArrayCollection
    {
        return $this->customers;
    }
}
