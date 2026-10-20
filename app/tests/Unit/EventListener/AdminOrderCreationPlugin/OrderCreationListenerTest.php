<?php

namespace App\Tests\Unit\EventListener\AdminOrderCreationPlugin;

use App\Component\Order\Processor\CompositeReorderProcessor;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\EventListener\AdminOrderCreationPlugin\OrderCreationListener;
use App\Services\OrderReturn\ReturnMailService;
use App\Services\ShippingMethod\ShippingMethodService;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SM\Factory\FactoryInterface;
use SM\SMException;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\InvalidArgumentException;

class OrderCreationListenerTest extends TestCase
{
    private OrderProcessorInterface $orderProcessor;

    private FactoryInterface $stateMachineFactory;

    private RequestStack $requestStack;

    private OrderCreationListener $listener;

    public function setUp(): void
    {
        $this->event = $this->createMock(GenericEvent::class);
        $this->orderProcessor = $this->createMock(OrderProcessorInterface::class);
        $this->stateMachineFactory = $this->createMock(FactoryInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->listener = new OrderCreationListener(
            $this->orderProcessor,
            $this->createMock(CompositeReorderProcessor::class),
            $this->stateMachineFactory,
            $this->createMock(OrderRepositoryInterface::class),
            $this->requestStack,
            $this->createMock(ReturnMailService::class),
            $this->createMock(ShippingMethodService::class),
            $this->createMock(MessageBusInterface::class),
        );
    }

    public function testProcessOrderBeforeCreationWithASubjectNotBeingAnOrder(): void
    {
        $event = new GenericEvent(new \stdClass());

        $this->expectException(InvalidArgumentException::class);

        $this->listener->processOrderBeforeCreation($event);
    }

    public function testProcessOrderBeforeCreationWithASubjectBeingAnOrder(): void
    {
        $order = $this->createMock(Order::class);

        $order
            ->expects($this->once())
            ->method('recalculateAdjustmentsTotal');

        $this->orderProcessor
            ->expects($this->once())
            ->method('process')
            ->with($order);

        $order
            ->expects($this->once())
            ->method('getParentNumber')
            ->willReturn(null);

        $this->listener->processOrderBeforeCreation(new GenericEvent($order));
    }

    /**
     * @throws SMException
     */
    public function testCompleteOrderBeforeCreationWithASubjectNotBeingAnOrder(): void
    {
        $event = new GenericEvent(new \stdClass());

        $this->expectException(InvalidArgumentException::class);

        $this->listener->completeOrderBeforeCreation($event);
    }

    /**
     * @throws SMException
     */
    public function testCompleteOrderBeforeCreationWithNoCurrentRequest(): void
    {
        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No current request found');

        $this->listener->completeOrderBeforeCreation(new GenericEvent(new Order()));
    }

    /**
     * @throws SMException
     */
    public function testCompleteOrderBeforeCreationWithASubjectBeingAnOrderWithoutAChannel(): void
    {
        $order = $this->createMock(Order::class);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(new Request());

        $order
            ->expects($this->once())
            ->method('getChannel')
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);

        $this->listener->completeOrderBeforeCreation(new GenericEvent($order));
    }

    /**
     * @throws SMException
     *
     * @dataProvider provideRequestWithFlags
     */
    public function testCompleteOrderBeforeCreationWithASubjectBeingAnOrderWithAChannel(Request $request): void
    {
        $order = new Order();

        $channel = new Channel();
        $channel->setCode('web');

        $order->setChannel($channel);

        $stateMachine = $this->createMock(StateMachineInterface::class);

        $this->stateMachineFactory
            ->expects($this->once())
            ->method('get')
            ->with($order, OrderCheckoutTransitions::GRAPH)
            ->willReturn($stateMachine);

        $expectedState = null;
        if (true === $request->request->get('toAddress')) {
            $this->setupStateMachineMockTransition($stateMachine, OrderCheckoutTransitions::TRANSITION_ADDRESS, $order);
            $expectedState = OrderCheckoutStates::STATE_ADDRESSED;
        }

        if (true === $request->request->get('toComplete')) {
            $this->setupStateMachineMockTransition($stateMachine, OrderCheckoutTransitions::TRANSITION_COMPLETE, $order);
            $expectedState = OrderCheckoutStates::STATE_COMPLETED;
        }

        if (true === $request->request->get('toSelectShipping')) {
            $this->setupStateMachineMockTransition($stateMachine, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING, $order);
            $expectedState = OrderCheckoutStates::STATE_SHIPPING_SELECTED;
        }

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->listener->completeOrderBeforeCreation(new GenericEvent($order));

        self::assertSame($expectedState, $order->getCheckoutState());
    }

    private function setupStateMachineMockTransition(StateMachineInterface&MockObject $stateMachine, string $transition, Order $order): void
    {
        $stateMachine
            ->method('can')
            ->willReturnMap([
                [OrderCheckoutTransitions::TRANSITION_ADDRESS, true],
                [OrderCheckoutTransitions::TRANSITION_COMPLETE, true],
                [OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING, true],
                [OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT, true],
            ]);

        $stateMachine
            ->method('apply')
            ->willReturnCallback(function () use ($order, $transition): bool {
                $order->setCheckoutState(match ($transition) {
                    OrderCheckoutTransitions::TRANSITION_ADDRESS => OrderCheckoutStates::STATE_ADDRESSED,
                    OrderCheckoutTransitions::TRANSITION_COMPLETE => OrderCheckoutStates::STATE_COMPLETED,
                    OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING => OrderCheckoutStates::STATE_SHIPPING_SELECTED,
                    OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT => OrderCheckoutStates::STATE_PAYMENT_SELECTED,
                });

                return true;
            });
    }

    public function provideRequestWithFlags(): iterable
    {
        $requestToAddress = new Request(request: ['toAddress' => true]);

        yield [$requestToAddress];

        $requestToComplete = new Request(request: ['toComplete' => true]);

        yield [$requestToComplete];

        $requestToSelectShipping = new Request(request: ['toSelectShipping' => true]);

        yield [$requestToSelectShipping];

        $requestWithMultipleFlags = new Request(request: ['toAddress' => true, 'toComplete' => true, 'toSelectShipping' => true]);

        yield [$requestWithMultipleFlags];

        $requestWithAddressAndCompleteFlags = new Request(request: ['toAddress' => true, 'toComplete' => true, 'toSelectShipping' => false]);

        yield [$requestWithAddressAndCompleteFlags];
    }
}
