<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Factory\ShipmentFactoryInterface;
use App\ReorderProcessing\BisExchangeParentProcessor;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Webmozart\Assert\InvalidArgumentException;

class BisExchangeParentProcessorTest extends TestCase
{
    private Order $order;
    private Order $reorder;
    private Order $initialOrder;
    /** @var MockObject|OrderRepositoryInterface */
    private $orderRepository;
    /** @var MockObject|ShipmentFactoryInterface */
    private $shipmentFactory;
    private BisExchangeParentProcessor $processor;

    private readonly EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
        $this->initialOrder = new Order();
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->shipmentFactory = $this->createMock(ShipmentFactoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->initialOrder->setNumber('initialNumber');

        $this->order->addShipment(new Shipment());
        $this->reorder->addShipment(new Shipment());
    }

    public function setUpProcessor()
    {
        $this->processor = new BisExchangeParentProcessor(
            $this->orderRepository,
            $this->shipmentFactory,
            $this->entityManager,
        );
    }

    public function setUpNoErrors()
    {
        $this->orderRepository
            ->method('findInitialOrder')
            ->willReturn($this->initialOrder);

        $this->shipmentFactory
            ->method('createFromExisting')
            ->willReturn(new Shipment());

        $this->setUpProcessor();
    }

    public function testProcess(): void
    {
        $this->setUpNoErrors();
        $this->processor->process($this->order, $this->reorder);

        $this->assertEquals($this->initialOrder->getNumber(), $this->reorder->getParentNumber());
    }

    public function testProcessorNoInitialOrderError(): void
    {
        $this->orderRepository
            ->method('findInitialOrder')
            ->willReturn(null);

        $this->setUpProcessor();
        $this->expectException(InvalidArgumentException::class);

        $this->processor->process($this->order, $this->reorder);
    }

    public function testProcessorNoInitialOrderNumberError(): void
    {
        $this->setUpNoErrors();
        $this->initialOrder->setNumber(null);

        $this->expectException(InvalidArgumentException::class);

        $this->processor->process($this->order, $this->reorder);
    }

    public function testDuplicateShipment(): void
    {
        $shipments = clone $this->reorder->getShipments();
        $this->setUpProcessor();

        $this->invokeMethod($this->processor, 'duplicateShipment', [$this->order, $this->reorder]);

        foreach ($shipments as $shipment) {
            $this->assertFalse($this->reorder->hasShipment($shipment));
        }

        $this->assertEquals(1, $this->reorder->getShipments()->count());
    }

    public function testDuplicateShipmentNoShipmentError(): void
    {
        $this->setUpProcessor();
        $this->order->getShipments()->clear();

        $this->expectException(InvalidArgumentException::class);

        $this->invokeMethod($this->processor, 'duplicateShipment', [$this->order, $this->reorder]);
    }
}
