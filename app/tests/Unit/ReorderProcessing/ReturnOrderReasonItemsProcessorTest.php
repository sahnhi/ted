<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\ReorderProcessing\ReturnOrderReasonItemsProcessor;
use App\Tests\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class ReturnOrderReasonItemsProcessorTest extends TestCase
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
        $this->processor = new ReturnOrderReasonItemsProcessor();
    }

    private function setUpNoErrors()
    {
        $orderReason = new OrderReason();
        $orderReason->setReason(new Reason());

        $this->reorder->addOrderReason($orderReason);

        $orderItem = new OrderItem();
        $orderItem->setVariant(new ProductVariant());
        $orderItem->getQuantity();
        $this->reorder->addItem($orderItem);

        $this->setUpProcessor();
    }

    public function testProcessNoOrderReason(): void
    {
        $this->setUpNoErrors();
        $this->reorder->getOrderReason()->clear();

        $this->processor->process($this->order, $this->reorder);
        $this->assertEquals(true, true);
    }

    public function testProcessNoItems(): void
    {
        $this->setUpNoErrors();
        $this->reorder->getItems()->clear();

        $this->processor->process($this->order, $this->reorder);

        foreach ($this->reorder->getOrderReason() as $orderReason) {
            if (null !== $orderReason->getReason()) {
                $this->assertNull($orderReason->getQuantity());
                $this->assertNull($orderReason->getVariant());
            }
        }
    }

    public function testProcess(): void
    {
        $this->setUpNoErrors();

        $this->processor->process($this->order, $this->reorder);

        foreach ($this->reorder->getOrderReason() as $key => $orderReason) {
            if (null !== $orderReason->getReason()) {
                if (isset($this->reorder->getItems()[$key])) {
                    $this->assertEquals($this->reorder->getItems()[$key]->getQuantity(), $orderReason->getQuantity());
                    $this->assertEquals($this->reorder->getItems()[$key]->getVariant(), $orderReason->getVariant());
                }
            }
        }
    }

    public function testProcessNoReasonError(): void
    {
        $this->setUpProcessor();
        $this->reorder->addOrderReason(new OrderReason());

        $this->expectException(InvalidArgumentException::class);

        $this->processor->process($this->order, $this->reorder);
    }
}
