<?php

namespace App\Tests\Unit\Api\Odoo\Factory;

use App\Api\Odoo\Factory\AddressFactoryInterface;
use App\Api\Odoo\Factory\CustomerFactoryInterface;
use App\Api\Odoo\Factory\DeliveryDateFactoryInterface;
use App\Api\Odoo\Factory\ItemFactoryInterface;
use App\Api\Odoo\Factory\OrderFactory;
use App\Api\Odoo\Factory\ShipmentItemFactoryInterface;
use App\Api\Odoo\Model\AddressInterface;
use App\Api\Odoo\Model\DeliveryDateInterface;
use App\Api\Odoo\Model\ItemInterface;
use App\Api\Odoo\Model\ShipmentItem;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\PaymentMethod;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Repository\ProductComboRepositoryInterface;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Provider\TaxRateProviderInterface;
use App\Services\Channel\ChannelCode;
use App\Services\Payment\TransactionIdFinder;
use App\Services\Shipment\LogisticItemsInterface;
use App\Tests\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Payment;
use Webmozart\Assert\InvalidArgumentException;

class OrderFactoryTest extends TestCase
{
    private OrderFactory $factory;
    /** @var CustomerFactoryInterface */
    private $customerFactory;
    /** @var AddressFactoryInterface */
    private $addressFactory;
    /** @var DeliveryDateFactoryInterface */
    private $deliveryDateFactory;
    /** @var ItemFactoryInterface */
    private $itemFactory;
    /** @var TransactionIdFinder */
    private $transactionIdFinder;
    /** @var LogisticItemsInterface */
    private $logisticItems;
    /** @var OrderRepositoryInterface */
    private $orderRepository;
    /** @var TaxRateProviderInterface */
    private $taxRateProvider;
    /** @var ProductVariantRepositoryInterface */
    private $productVariantRepository;
    /** @var ProductComboRepositoryInterface */
    private $productComboRepository;
    private Order $order;
    private ShipmentItemFactoryInterface $shipmentItemFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerFactory = $this->createMock(CustomerFactoryInterface::class);
        $this->addressFactory = $this->createMock(AddressFactoryInterface::class);
        $this->deliveryDateFactory = $this->createMock(DeliveryDateFactoryInterface::class);
        $this->itemFactory = $this->createMock(ItemFactoryInterface::class);
        $this->transactionIdFinder = $this->createMock(TransactionIdFinder::class);
        $this->logisticItems = $this->createMock(LogisticItemsInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->taxRateProvider = $this->createMock(TaxRateProviderInterface::class);
        $this->productVariantRepository = $this->createMock(ProductVariantRepositoryInterface::class);
        $this->productComboRepository = $this->createMock(ProductComboRepositoryInterface::class);
        $this->shipmentItemFactory = $this->createMock(ShipmentItemFactoryInterface::class);
    }

    public function setUpFactory()
    {
        $this->factory = new OrderFactory(
            $this->customerFactory,
            $this->addressFactory,
            $this->deliveryDateFactory,
            $this->itemFactory,
            $this->transactionIdFinder,
            $this->logisticItems,
            $this->orderRepository,
            $this->taxRateProvider,
            $this->productVariantRepository,
            $this->productComboRepository,
            $this->shipmentItemFactory,
        );
    }

    private function setUpCreateNew()
    {
        $this->order = new Order();
        $this->order->setShippingAddress(new Address());
        $this->order->setCustomer(new Customer());
        $this->order->setChannel(new Channel());

        $customerMock = $this->createMock(\App\Api\Odoo\Model\Customer::class);
        $customerMock->firstname = 'john';
        $customerMock->lastname = 'doe';

        $this->customerFactory
            ->method('createNew')
            ->willReturn($customerMock);

        $this->addressFactory
            ->method('createNew')
            ->willReturn($this->createMock(AddressInterface::class));

        $this->deliveryDateFactory
            ->method('createNew')
            ->willReturn($this->createMock(DeliveryDateInterface::class));

        $this->itemFactory
            ->method('createNew')
            ->willReturn($this->createMock(ItemInterface::class));

        $this->setUpFactory();
    }

    public function testCreateNew(): void
    {
        $this->setUpCreateNew();
        $order = $this->factory->createNew($this->order);

        $this->assertInstanceOf(\App\Api\Odoo\Model\OrderInterface::class, $order);
    }

    public function testCreateWithShipment(): void
    {
        $this->setUpCreateNew();
        $shipment = new Shipment();
        $shipment->setShippingDate(new \DateTime());
        $shipment->setMethod(new ShippingMethod());
        $this->order->addShipment($shipment);

        $order = $this->factory->createNew($this->order);
        $this->assertInstanceOf(\App\Api\Odoo\Model\OrderInterface::class, $order);
    }

    public function testCreateNewWithPayment(): void
    {
        $this->setUpCreateNew();
        $method = new PaymentMethod();
        $payment = new Payment();
        $payment->setMethod($method);
        $this->order->addPayment($payment);

        $order = $this->factory->createNew($this->order);
        $this->assertInstanceOf(\App\Api\Odoo\Model\OrderInterface::class, $order);
    }

    public function testCreateNewFail(): void
    {
        $this->setUpCreateNew();

        $this->expectException(InvalidArgumentException::class);
        $this->factory->createNew($this->order, Order::class);
    }

    public function testInitItems(): void
    {
        $this->setUpCreateNew();

        $items = $this->invokeMethod($this->factory, 'initItems', [$this->order]);
        $this->assertEmpty($items);
    }

    public function testInitItemsNotEmpty(): void
    {
        $this->setUpCreateNew();
        $item = new OrderItem();
        $this->order->addItem($item);

        $items = $this->invokeMethod($this->factory, 'initItems', [$this->order]);

        $this->assertNotEmpty($items);
        $this->assertInstanceOf(ItemInterface::class, $items->first());
    }

    public function testInitItemsWithShipment(): void
    {
        $this->setUpCreateNew();

        $shippingMethodMock = $this->createMock(ShippingMethod::class);
        $shippingMethodMock->method('getCode')->willReturn('code');

        $shipmentMock = $this->createMock(Shipment::class);
        $shipmentMock->method('getMethod')->willReturn($shippingMethodMock);

        $adjustementMock = $this->createMock(AdjustmentInterface::class);
        $adjustementMock->method('getType')->willReturn(AdjustmentInterface::SHIPPING_ADJUSTMENT);
        $adjustementMock->method('getAmount')->willReturn(100);

        $this->order->addShipment($shipmentMock);
        $this->order->addAdjustment($adjustementMock);

        $erpOrder = $this->factory->createNew($this->order);
        $this->assertNotEmpty($erpOrder->items);
        $this->assertInstanceOf(ShipmentItem::class, $erpOrder->items->first());
    }

    public function testCreateNewOrderShipment(): void
    {
        $this->setUpCreateNew();

        $this->logisticItems
            ->method('getLogisticItemsComboFromOrder')
            ->willReturn([new ArrayCollection([new OrderItem()]), []]);

        $shipment = new Shipment();
        $this->order->addShipment($shipment);

        $channel = new Channel();
        $channel->setCode(ChannelCode::CHANNEL_WEB);
        $this->order->setChannel($channel);

        $method = new ShippingMethod();
        $method->setCode('code');

        $shipment = new Shipment();
        $shipment->setShippingDate(new \DateTime());
        $shipment->setMethod($method);
        $this->order->addShipment($shipment);

        $order = $this->factory->createNewOrderShipment($this->order);

        $this->assertInstanceOf(\App\Api\Odoo\Model\OrderInterface::class, $order);
    }

    public function testCreateNewOrderShipmentWrongClassError(): void
    {
        $this->setUpCreateNew();

        $this->expectException(InvalidArgumentException::class);
        $this->factory->createNew($this->order, Order::class);
    }

    public function testCreateNewOrderShipmentNoItemError(): void
    {
        $this->setUpCreateNew();

        $this->logisticItems
            ->method('getLogisticItemsComboFromOrder')
            ->willReturn([new ArrayCollection([new OrderItem()]), []]);

        $this->expectException(InvalidArgumentException::class);
        $this->factory->createNewOrderShipment($this->order);
    }

    public function testCreateNewOrderStock(): void
    {
        $this->setUpCreateNew();

        $this->logisticItems
            ->method('getLogisticItemsComboFromOrder')
            ->willReturn([new ArrayCollection([new OrderItem()]), []]);

        $order = $this->factory->createNewOrderStock($this->order);

        $this->assertInstanceOf(\App\Api\Odoo\Model\OrderInterface::class, $order);
    }

    public function testCreateNewOrderStockWrongClassError(): void
    {
        $this->setUpCreateNew();

        $this->logisticItems
            ->method('getLogisticItemsComboFromOrder')
            ->willReturn([new ArrayCollection([new OrderItem()]), []]);

        $this->expectException(InvalidArgumentException::class);
        $this->factory->createNewOrderStock($this->order, Order::class);
    }

    public function testCreateNewOrderStockNoItemError(): void
    {
        $this->setUpCreateNew();

        $this->logisticItems
            ->method('getLogisticItemsComboFromOrder')
            ->willReturn([new ArrayCollection(), []]);

        $this->expectException(InvalidArgumentException::class);
        $this->factory->createNewOrderStock($this->order);
    }
}
