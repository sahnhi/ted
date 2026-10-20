<?php

namespace App\Tests\Unit\Api\Odoo\Model;

use App\Api\Odoo\Model\AddressInterface;
use App\Api\Odoo\Model\Customer;
use App\Api\Odoo\Model\CustomerInterface;
use App\Api\Odoo\Model\DeliveryDateInterface;
use App\Api\Odoo\Model\ItemInterface;
use App\Api\Odoo\Model\Order;
use App\Doctrine\Entity\OrderStore;
use App\Doctrine\Entity\Store;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Doctrine\Entity\Sylius\PaymentMethod;
use App\Doctrine\Entity\Sylius\Promotion;
use App\Services\Channel\ChannelCode;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\Payment;

class OrderTest extends TestCase
{
    /** @var Order */
    private $order;

    public function setUpModel(
        OrderInterface $order,
        CustomerInterface $customer,
        AddressInterface $shippingAddress,
        AddressInterface $invoicingAddress,
        DeliveryDateInterface $deliveryDate,
        ArrayCollection $items,
        ?string $paymentId = null
    ): void {
        $this->order = new Order(
            $order,
            $items,
            $shippingAddress,
            $invoicingAddress,
            $deliveryDate,
            $customer,
            $paymentId
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

        $this->assertNull($this->order->store);
        $this->assertNull($this->order->paymentId);
        $this->assertNull($this->order->paymentMethod);
        $this->assertEmpty($this->order->promotions);
        $this->assertEquals($order->getTotal(), $this->order->totalTTC);
        $this->assertEquals($order->getTaxTotal() - $order->getTaxTotal(), $this->order->totalHT);
        $this->assertEquals($order->getTaxTotal(), $this->order->totalTaxes);
    }

    public function testConstructWithStore(): void
    {
        /** @var BaseOrder $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstruct();

        $store = new Store();
        $orderStore = new OrderStore();

        $store->setName('name');
        $orderStore->setStore($store);
        $order->setOrderStore($orderStore);

        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);
        $this->assertEquals($store->getName(), $this->order->store);
    }

    public function testConstructWithPayments(): void
    {
        /** @var BaseOrder $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstruct();

        $payment = new Payment();
        $method = new PaymentMethod();

        $method->setCode('code');
        $payment->setMethod($method);
        $order->addPayment($payment);

        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);
        $this->assertEquals($method->getCode(), $this->order->paymentMethod);
    }

    public function testConstructWithPaymentsWithTransactionId(): void
    {
        /** @var BaseOrder $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstruct();

        $payment = new Payment();
        $method = new PaymentMethod();

        $payment->setMethod($method);
        $payment->setDetails(['TRANSACTIONID' => 'transactionID']);
        $order->addPayment($payment);

        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items, 'transactionID');
        $this->assertEquals($payment->getDetails()['TRANSACTIONID'], $this->order->paymentId);
    }

    public function testConstructWithPromotions(): void
    {
        /** @var BaseOrder $order */
        [$order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items] = $this->setUpConstruct();

        $promotion = new Promotion();
        $promotion->setCode('code');

        $order->addPromotion($promotion);

        $this->setUpModel($order, $customer, $shippingAddress, $invoicingAddress, $deliveryDate, $items);

        foreach ($order->getPromotions() as $promotion) {
            $found = false;
            /** @var \App\Api\Odoo\Model\Promotion $odooPromotion */
            foreach ($this->order->promotions as $odooPromotion) {
                $found = $promotion->getCode() === $odooPromotion->name ? true : $found;
            }

            $this->assertTrue($found);
        }
    }
}
