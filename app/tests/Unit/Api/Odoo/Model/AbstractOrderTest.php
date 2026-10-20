<?php

namespace App\Tests\Unit\Api\Odoo\Model;

use App\Api\Odoo\Model\AbstractOrder;
use App\Api\Odoo\Model\AddressInterface;
use App\Api\Odoo\Model\Customer;
use App\Api\Odoo\Model\CustomerInterface;
use App\Api\Odoo\Model\DeliveryDateInterface;
use App\Api\Odoo\Model\ItemInterface;
use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\OrderStore;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Seller;
use App\Doctrine\Entity\Store;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Warehouse;
use App\Services\Channel\ChannelCode;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;

class AbstractOrderTest extends TestCase
{
    /** @var AbstractOrder */
    private $order;

    /**
     * @param ArrayCollection<ItemInterface> $items
     *
     * @throws \ReflectionException
     */
    public function setUpModel(
        OrderInterface $order,
        CustomerInterface $customer,
        ?AddressInterface $shippingAddress,
        AddressInterface $invoicingAddress,
        DeliveryDateInterface $deliveryDate,
        ArrayCollection $items
    ): void {
        $this->order = $this->getMockForAbstractClass(AbstractOrder::class, [
            $order,
            $items,
            $shippingAddress,
            $invoicingAddress,
            $deliveryDate,
            $customer,
        ]);
    }

    /**
     * @throws \ReflectionException
     */
    private function setUpConstruct(): array
    {
        $channel = new Channel();
        $channel->setCode(ChannelCode::CHANNEL_WEB);

        $order = new Order();
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

    /**
     * @throws \ReflectionException
     */
    private function setUpConstructAsReturn($returnType = Reason::RETURN_TYPE_SENDER): array
    {
        $channel = new Channel();
        $channel->setCode(ChannelCode::CHANNEL_RETURN);

        $order = new Order();
        $customer = $this->createMock(Customer::class);
        $invoicingAddress = $this->createMock(AddressInterface::class);
        $deliveryDate = $this->createMock(DeliveryDateInterface::class);
        $items = new ArrayCollection([$this->createMock(ItemInterface::class)]);
        $orderReason = $this->createMock(OrderReason::class);
        $reason = $this->createMock(Reason::class);
        $orderReason->method('getReason')->willReturn($reason);
        $reason->method('getReturnType')->willReturn($returnType);

        $order->setChannel($channel);
        $order->setNumber('number');
        $order->setCheckoutCompletedAt(new \DateTime());
        $order->addOrderReason($orderReason);

        return [$order, $customer, null, $invoicingAddress, $deliveryDate, $items];
    }

    public function testConstruct(): void
    {
        /** @var Order $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstruct();
        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);

        $this->assertEquals($customer, $this->order->customer);
        $this->assertEquals($shippingAddress, $this->order->shippingAddress);
        $this->assertEquals($invoicingAddress, $this->order->invoicingAddress);
        $this->assertEquals($order->getCheckoutCompletedAt(), $this->order->confirmationDate);
        $this->assertEquals($deliveryDate, $this->order->deliveryDate);
        $this->assertEquals($order->getNumber(), $this->order->name);
        $this->assertNull($this->order->salesman);
        $this->assertEquals('web', $this->order->channel);
        $this->assertEquals('', $this->order->warehouse);
        $this->assertNull($this->order->deliveryMethod);
        $this->assertEquals($order->getCheckoutCompletedAt(), $this->order->shippingDate);
        $this->assertEquals($items, $this->order->items);
    }

    public function testConstructWithStoreAndSeller(): void
    {
        /** @var Order $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstruct();

        $store = new Store();
        $orderStore = new OrderStore();

        $seller = new Seller();
        $seller->setName('name');

        $orderStore->setSeller($seller);
        $orderStore->setStore($store);
        $order->setOrderStore($orderStore);

        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);
        $this->assertEquals($seller->getName(), $this->order->salesman);
    }

    public function testConstructWithShipment(): void
    {
        /** @var Order $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstruct();

        $shipment = new Shipment();
        $method = new ShippingMethod();

        $method->setCode('code');
        $shipment->setMethod($method);
        $shipment->setShippingDate(new \DateTime());
        $order->addShipment($shipment);

        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);
        $this->assertEquals($method->getCode(), $this->order->deliveryMethod);
        $this->assertEquals($shipment->getShippedAt(), $this->order->shippingDate);
    }

    public function testConstructWithShipmentWithWarehouse(): void
    {
        /** @var Order $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstruct();

        $shipment = new Shipment();
        $method = new ShippingMethod();
        $warehouse = new Warehouse();

        $warehouse->setName('Name');
        $method->setWarehouse($warehouse);
        $shipment->setMethod($method);
        $shipment->setShippingDate(new \DateTime());
        $order->addShipment($shipment);

        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);
        $this->assertEquals(strtolower((string) $warehouse->getName()), $this->order->warehouse);
    }

    public function testConstructWithShipmentWithWarehouseOnRXReturn(): void
    {
        /** @var Order $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstructAsReturn();

        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);
        $this->assertEquals('Quai77', $this->order->warehouse);
    }

    public function testConstructWithShipmentWithWarehouseOnMostReturn(): void
    {
        /** @var Order $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstructAsReturn(Reason::RETURN_TYPE_REFUND);

        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);
        $this->assertEquals('', $this->order->warehouse);
    }
}
