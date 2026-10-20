<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\ReorderProcessing\NumberProcessor;
use App\Services\Channel\ChannelCode;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Component\Core\Model\Channel;
use Webmozart\Assert\InvalidArgumentException;

class NumberProcessorTest extends TestCase
{
    public const TEST_PARENT_NUMBER = '000001';
    public const TEST_NUMBER = '000001B1';

    /** @var Order */
    private $order;

    /** @var Order */
    private $reorder;

    /** @var MockObject|OrderRepositoryInterface */
    private $orderRepository;

    /** @var ReturnTypeServiceInterface */
    private $returnTypeService;

    /** @var NumberProcessor */
    private $processor;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->returnTypeService = $this->createMock(ReturnTypeServiceInterface::class);

        $this->order->setNumber(self::TEST_PARENT_NUMBER);
        $this->order->setBis(0);
        $this->order->setReturn(0);
    }

    private function setUpProcessor()
    {
        $this->processor = new NumberProcessor(
            $this->orderRepository,
            $this->returnTypeService
        );
    }

    private function setUpNoErrorsBis()
    {
        $channel = new Channel();
        $channel->setCode(ChannelCode::CHANNEL_BIS);
        $this->reorder->setChannel($channel);

        $this->orderRepository
            ->method('getBisNumber')
            ->willReturn($this->order->getBis());

        $this->setUpProcessor();
    }

    private function setUpNoErrorsReturn()
    {
        $channel = new Channel();
        $channel->setCode(ChannelCode::CHANNEL_RETURN);
        $this->reorder->setChannel($channel);

        $this->orderRepository
            ->method('getReturnNumber')
            ->willReturn($this->order->getReturn());

        $this->returnTypeService
            ->method('getReturnOrderPrefix')
            ->willReturn(ReturnTypeServiceInterface::RETURN_PREFIXES[Reason::RETURN_TYPE_NORMAL]);

        $this->setUpProcessor();
    }

    public function testProcessBis(): void
    {
        $this->setUpNoErrorsBis();
        $this->processor->process($this->order, $this->reorder);

        $this->assertTrue(true);
    }

    public function testProcessReturn(): void
    {
        $this->setUpNoErrorsReturn();
        $this->processor->process($this->order, $this->reorder);

        $this->assertTrue(true);
    }

    public function testProcessChannelCodeNull(): void
    {
        $this->setUpNoErrorsReturn();
        $this->reorder->getChannel()->setCode(null);

        $this->processor->process($this->order, $this->reorder);

        $this->assertTrue(true);
    }

    public function testProcessChannelNullError(): void
    {
        $this->setUpProcessor();

        $this->expectException(InvalidArgumentException::class);
        $this->processor->process($this->order, $this->reorder);
    }

    public function testHandleBisNumber(): void
    {
        $this->setUpNoErrorsBis();
        $this->invokeMethod($this->processor, 'handleBisNumber', [$this->order, $this->reorder]);

        $this->assertEquals($this->order->getBis() + 1, $this->reorder->getBis());
        $this->assertEquals($this->order->getNumber(), $this->reorder->getParentNumber());
        $this->assertEquals($this->order->getNumber() . NumberProcessor::BIS_PREFIX . $this->reorder->getBis(), $this->reorder->getNumber());
    }

    public function testHandleBisNumberWithParent(): void
    {
        $this->order->setNumber(self::TEST_NUMBER);
        $this->order->setParentNumber(self::TEST_PARENT_NUMBER);
        $this->order->setBis(1);
        $this->setUpNoErrorsBis();

        $this->invokeMethod($this->processor, 'handleBisNumber', [$this->order, $this->reorder]);

        $this->assertEquals($this->order->getBis() + 1, $this->reorder->getBis());
        $this->assertEquals($this->order->getParentNumber(), $this->reorder->getParentNumber());
        $this->assertEquals($this->order->getParentNumber() . NumberProcessor::BIS_PREFIX . $this->reorder->getBis(), $this->reorder->getNumber());
    }

    public function testHandleReturnNumber(): void
    {
        $this->setUpNoErrorsReturn();
        $this->invokeMethod($this->processor, 'handleReturnNumber', [$this->order, $this->reorder]);

        $this->assertEquals($this->order->getReturn() + 1, $this->reorder->getReturn());
        $this->assertEquals($this->order->getNumber(), $this->reorder->getParentNumber());
        $this->assertEquals($this->order->getNumber() . ReturnTypeServiceInterface::RETURN_PREFIXES[Reason::RETURN_TYPE_NORMAL] . $this->reorder->getReturn(), $this->reorder->getNumber());
    }

    public function testHandleReturnNumberNoPrefixError(): void
    {
        $this->returnTypeService
            ->method('getReturnOrderPrefix')
            ->willReturn(null);

        $this->setUpNoErrorsReturn();
        $this->expectException(InvalidArgumentException::class);
        $this->invokeMethod($this->processor, 'handleReturnNumber', [$this->order, $this->reorder]);
    }
}
