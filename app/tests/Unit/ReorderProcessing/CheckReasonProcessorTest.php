<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\ReorderProcessing\CheckReasonProcessor;
use App\Tests\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class CheckReasonProcessorTest extends TestCase
{
    /** @var Order */
    private $order;

    /** @var Order */
    private $reorder;

    /** @var CheckReasonProcessor */
    private $processor;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
    }

    public function setUpProcessor()
    {
        $this->processor = new CheckReasonProcessor();
    }

    private function setUpNoErrors()
    {
        $reason = new Reason();
        $orderReason = new OrderReason();
        $orderReason->setReason($reason);

        $this->reorder->addOrderReason($orderReason);
        $this->setUpProcessor();
    }

    public function testProcess(): void
    {
        $this->setUpNoErrors();
        $this->processor->process($this->order, $this->reorder);

        $this->assertTrue(true);
    }

    public function testProcessEmptyReasons(): void
    {
        $this->setUpNoErrors();
        $this->reorder->getOrderReason()->clear();
        $this->processor->process($this->order, $this->reorder);

        $this->assertTrue(true);
    }

    public function testProcessReasonNullError(): void
    {
        $this->setUpNoErrors();
        $this->reorder->getOrderReason()->first()->setReason(null);

        $this->expectException(InvalidArgumentException::class);

        $this->processor->process($this->order, $this->reorder);
    }
}
