<?php

namespace App\Tests\Unit\Api\Odoo\Model;

use App\Api\Odoo\Model\Item;
use App\Api\Odoo\Model\OrderShipment;
use App\Doctrine\Entity\Sylius\Order as BaseOrder;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Services\Channel\ChannelCode;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\InvalidArgumentException;

class OrderShipmentTest extends TestCase
{
    /** @var OrderShipment */
    private $order;
    private $clear;

    public function setUpModel(
        OrderInterface $order,
        ArrayCollection $items
    ): void {
        $this->order = new OrderShipment(
            $order,
            $items
        );
    }

    private function setUpConstruct(): array
    {
        $order = new BaseOrder();
        $items = new ArrayCollection([$this->createMock(Item::class)]);

        $order->setNumber('number');
        $order->setCheckoutCompletedAt(new \DateTime());

        $channel = new Channel();
        $channel->setCode(ChannelCode::CHANNEL_WEB);
        $order->setChannel($channel);

        $method = new ShippingMethod();
        $method->setCode('code');

        $shipment = new Shipment();
        $shipment->setShippingDate(new \DateTime());
        $shipment->setMethod($method);
        $order->addShipment($shipment);

        return [$order, $items];
    }

    public function testConstruct(): void
    {
        /** @var BaseOrder $order */
        [$order, $items] = $this->setUpConstruct();

        $order->setNumber('003519226');

        $this->setUpModel($order, $items);

        $this->assertEquals('003519226', $this->order->name);
        $this->assertEquals($items, $this->order->items);
        $this->assertEquals($order->getShipments()->last()->getShippingDate(), $this->order->dateDone);
    }

    public function testConstructNoShipment(): void
    {
        /** @var BaseOrder $order */
        [$order, $items] = $this->setUpConstruct();
        $order->getShipments()->clear();

        $this->expectException(InvalidArgumentException::class);
        $this->setUpModel($order, $items);
    }
}
