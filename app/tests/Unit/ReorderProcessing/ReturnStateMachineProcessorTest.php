<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\ReorderProcessing\ReturnStateMachineProcessor;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use App\Tests\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ReturnStateMachineProcessorTest extends TestCase
{
    /** @var Order */
    private $order;

    /** @var Order */
    private $reorder;

    /** @var ReturnTypeServiceInterface */
    private $returnTypeService;

    /** @var RequestStack */
    private $requestStack;

    /** @var ReturnStateMachineProcessor */
    private $processor;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
        $this->returnTypeService = $this->createMock(ReturnTypeServiceInterface::class);
        $this->requestStack = new RequestStack();

        $request = new Request();
        $this->requestStack->push($request);
    }

    private function setUpProcessor()
    {
        $this->processor = new ReturnStateMachineProcessor(
            $this->returnTypeService,
            $this->requestStack
        );
    }

    private function setUpNoErrors()
    {
        $channel = new Channel();
        $channel->setCode('return');

        $this->reorder->setChannel($channel);

        $this->returnTypeService
            ->method('getReturnType')
            ->willReturn(Reason::RETURN_TYPE_SENDER);

        $this->setUpProcessor();
    }

    public function testProcessSender(): void
    {
        $this->setUpNoErrors();

        $this->processor->process($this->order, $this->reorder);

        $this->assertFalse($this->requestStack->getCurrentRequest()->get('toAddress'));
        $this->assertFalse($this->requestStack->getCurrentRequest()->get('toComplete'));

        $this->assertEquals(OrderInterface::STATE_FULFILLED, $this->reorder->getState());
        $this->assertEquals(OrderPaymentStates::STATE_PAID, $this->reorder->getPaymentState());
        $this->assertEquals(OrderShippingStates::STATE_SHIPPED, $this->reorder->getShippingState());
        $this->assertEquals(OrderCheckoutStates::STATE_COMPLETED, $this->reorder->getCheckoutState());
        $this->assertInstanceOf(\DateTime::class, $this->reorder->getCheckoutCompletedAt());
    }

    public function testProcessRefund(): void
    {
        $this->returnTypeService
            ->method('getReturnType')
            ->willReturn(Reason::RETURN_TYPE_REFUND);
        $this->setUpNoErrors();

        $this->processor->process($this->order, $this->reorder);

        $this->assertFalse($this->requestStack->getCurrentRequest()->get('toAddress'));
        $this->assertFalse($this->requestStack->getCurrentRequest()->get('toComplete'));

        $this->assertEquals(OrderInterface::STATE_FULFILLED, $this->reorder->getState());
        $this->assertEquals(OrderPaymentStates::STATE_PAID, $this->reorder->getPaymentState());
        $this->assertEquals(OrderShippingStates::STATE_SHIPPED, $this->reorder->getShippingState());
        $this->assertEquals(OrderCheckoutStates::STATE_COMPLETED, $this->reorder->getCheckoutState());
        $this->assertInstanceOf(\DateTime::class, $this->reorder->getCheckoutCompletedAt());
    }

    public function testChannelNotReturn(): void
    {
        $this->setUpNoErrors();
        $this->reorder->getChannel()->setCode(null);

        $this->processor->process($this->order, $this->reorder);

        $this->assertNull($this->requestStack->getCurrentRequest()->get('toAddress'));
        $this->assertNull($this->requestStack->getCurrentRequest()->get('toComplete'));

        $this->assertEquals(OrderInterface::STATE_CART, $this->reorder->getState());
        $this->assertEquals(OrderPaymentStates::STATE_CART, $this->reorder->getPaymentState());
        $this->assertEquals(OrderShippingStates::STATE_CART, $this->reorder->getShippingState());
        $this->assertEquals(OrderCheckoutStates::STATE_CART, $this->reorder->getCheckoutState());
        $this->assertNull($this->reorder->getCheckoutCompletedAt());
    }

    public function testChannelWrongReason(): void
    {
        $this->returnTypeService
            ->method('getReturnType')
            ->willReturn(Reason::RETURN_TYPE_NORMAL);

        $this->setUpNoErrors();

        $this->processor->process($this->order, $this->reorder);

        $this->assertNull($this->requestStack->getCurrentRequest()->get('toAddress'));
        $this->assertNull($this->requestStack->getCurrentRequest()->get('toComplete'));

        $this->assertEquals(OrderInterface::STATE_CART, $this->reorder->getState());
        $this->assertEquals(OrderPaymentStates::STATE_CART, $this->reorder->getPaymentState());
        $this->assertEquals(OrderShippingStates::STATE_CART, $this->reorder->getShippingState());
        $this->assertEquals(OrderCheckoutStates::STATE_CART, $this->reorder->getCheckoutState());
        $this->assertNull($this->reorder->getCheckoutCompletedAt());
    }
}
