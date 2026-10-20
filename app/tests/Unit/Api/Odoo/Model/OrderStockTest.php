<?php

namespace App\Tests\Unit\Api\Odoo\Model;

use App\Api\Odoo\Model\AddressInterface;
use App\Api\Odoo\Model\Customer;
use App\Api\Odoo\Model\DeliveryDateInterface;
use App\Api\Odoo\Model\ItemInterface;
use App\Api\Odoo\Model\OrderStock;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Services\Channel\ChannelCode;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;

class OrderStockTest extends TestCase
{
    /** @var OrderStock */
    private $order;

    /**
     * @param ArrayCollection<ItemInterface> $items
     *
     * @throws \ReflectionException
     */
    public function setUpModel(
        OrderInterface $order,
        Customer $customer,
        AddressInterface $shippingAddress,
        AddressInterface $invoicingAddress,
        DeliveryDateInterface $deliveryDate,
        ArrayCollection $items
    ): void {
        $this->order = new OrderStock(
            $order,
            $items,
            $shippingAddress,
            $invoicingAddress,
            $deliveryDate,
            $customer
        );
    }

    /**
     * @throws \ReflectionException
     */
    private function setUpConstruct(): array
    {
        $channel = new Channel();
        $channel->setCode(ChannelCode::CHANNEL_WEB);

        $order = new BaseOrder();
        $customer = $this->createMock(Customer::class);
        $shippingAddress = $this->createMock(AddressInterface::class);
        $invoicingAddress = $this->createMock(AddressInterface::class);
        $deliveryDate = $this->createMock(DeliveryDateInterface::class);
        $items = new ArrayCollection([$this->createMock(ItemInterface::class)]);

        $order->setChannel($channel);
        $order->setNumber('number');
        $order->setCheckoutCompletedAt(new \DateTime());

        return [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items];
    }

    public function testConstruct(): void
    {
        /** @var BaseOrder $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstruct();
        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);

        $this->assertEquals('', $this->order->suffix);
    }

    public function testConstructBis(): void
    {
        /** @var BaseOrder $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstruct();
        $order->setBis(1);
        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);

        $this->assertEquals('B1', $this->order->suffix);
    }
}
