<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\ReorderProcessing\CleanShipmentsProcessor;
use App\Tests\TestCase;

class CleanShipmentsProcessorTest extends TestCase
{
    /** @var Order */
    private $order;

    /** @var Order */
    private $reorder;

    /** @var CleanShipmentsProcessor */
    private $processor;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
    }

    public function setUpProcessor()
    {
        $this->processor = new CleanShipmentsProcessor();
    }

    private function setUpNoErrors()
    {
        $shipment = new Shipment();
        $otherShipment = new Shipment();

        $this->reorder->addShipment($shipment);
        $this->reorder->addShipment($otherShipment);

        $this->setUpProcessor();
    }

    public function testProcess(): void
    {
        $this->setUpNoErrors();

        $shipments = $this->reorder->getShipments()->toArray();
        $firstShipment = array_shift($shipments);

        $this->processor->process($this->order, $this->reorder);

        $this->assertEquals(1, $this->reorder->getShipments()->count());
        $this->assertEquals($firstShipment, $this->reorder->getShipments()->first());

        foreach ($shipments as $shipment) {
            $this->assertFalse($this->reorder->hasShipment($shipment));
        }
    }

    public function testProcessNoShipment(): void
    {
        $this->setUpNoErrors();
        $this->reorder->getShipments()->clear();

        $this->processor->process($this->order, $this->reorder);
        $this->assertTrue(true);
    }
}
