<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\Shipment;
use App\ReorderProcessing\ReturnRemoveShipmentsProcessor;
use App\Tests\TestCase;

class ReturnRemoveShipmentsProcessorTest extends TestCase
{
    /** @var Order */
    private $order;

    /** @var Order */
    private $reorder;

    /** @var ReturnOrderReasonItemsProcessor */
    private $processor;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
    }

    private function setUpProcessor()
    {
        $this->processor = new ReturnRemoveShipmentsProcessor();
    }

    private function setUpNoErrors()
    {
        $shipment = new Shipment();
        $orderItem = new OrderItem();
        $orderUnit = new OrderItemUnit($orderItem);

        $orderUnit->setShipment($shipment);

        $this->reorder->addShipment($shipment);
        $this->reorder->addItem($orderItem);

        $this->setUpProcessor();
    }

    public function testProcess(): void
    {
        $this->setUpNoErrors();
        $this->processor->process($this->order, $this->reorder);

        $this->assertEmpty($this->reorder->getShipments());
    }

    public function testProcessNoShipments(): void
    {
        $this->setUpProcessor();
        $this->processor->process($this->order, $this->reorder);

        $this->assertEmpty($this->reorder->getShipments());
    }

    public function testProcessNoItems(): void
    {
        $this->setUpNoErrors();
        $this->reorder->getItems()->clear();

        $this->processor->process($this->order, $this->reorder);

        $this->assertEmpty($this->reorder->getShipments());
        $this->assertEmpty($this->reorder->getItems());
    }
}
