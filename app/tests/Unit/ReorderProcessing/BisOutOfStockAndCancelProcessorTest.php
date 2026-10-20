<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Component\Core\OrderShippingStates;
use App\Doctrine\Entity\OrderOutOfStock;
use App\Doctrine\Entity\OrderOutOfStockInterface;
use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\OrderVip;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PaymentMethod;
use App\Doctrine\Repository\OrderOutOfStockRepository;
use App\ReorderProcessing\BisOutOfStockAndCancelProcessor;
use App\Services\OutOfStock\WeightingCalculationInterface;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SM\Factory\Factory;
use SM\SMException;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Channel\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Payment\Model\Payment;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\InvalidArgumentException;

class BisOutOfStockAndCancelProcessorTest extends TestCase
{
    /** @var MockObject|RequestStack */
    private $requestStack;

    /** @var MockObject|Factory */
    private $smFactory;

    /** @var MockObject|FlashBagInterface */
    private $session;

    /** @var MockObject|RepositoryInterface */
    private $orderOutOfStockRepository;

    /** @var MockObject|RepositoryInterface */
    private $paymentMethodRepository;

    /** @var MockObject|WeightingCalculationInterface */
    private $weightingCalculation;

    /** @var TranslatorInterface */
    private $translator;

    /** @var BisOutOfStockAndCancelProcessor */
    private $processor;

    /** @var Order */
    private $order;

    /** @var Order */
    private $reorder;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();

        $this->requestStack = new RequestStack();
        $this->smFactory = $this->createMock(Factory::class);
        $this->session = $this->createMock(FlashBagInterface::class);
        $this->orderOutOfStockRepository = $this->createMock(OrderOutOfStockRepository::class);
        $this->paymentMethodRepository = $this->createMock(RepositoryInterface::class);
        $this->weightingCalculation = $this->createMock(WeightingCalculationInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $channel = new Channel();
        $channel->setCode('bis');
        $this->reorder->setChannel($channel);
        $this->reorder->setCurrencyCode('EUR');
    }

    private function setUpProcessor()
    {
        $this->processor = new BisOutOfStockAndCancelProcessor(
            $this->requestStack,
            $this->smFactory,
            $this->session,
            $this->orderOutOfStockRepository,
            $this->paymentMethodRepository,
            $this->weightingCalculation,
            $this->translator
        );
    }

    private function setUpNoErrors($formParams = [])
    {
        $request = new Request();
        $request->query->set('sylius_admin_order_creation_new_order', $formParams);
        $this->requestStack->push($request);

        $this->setUpProcessor();
    }

    private function setUpCancelledNoErrors($formParams = [])
    {
        $this->smFactory
            ->method('get')
            ->willReturn($stateMachine = $this->createMock(StateMachineInterface::class));

        $stateMachine
            ->method('apply')
            ->willReturnCallback(function (): bool {
                $this->order->setState(OrderInterface::STATE_CANCELLED);

                return true;
            });

        $reason = new OrderReason();
        $reason->setCreatedAt(new \DateTime());
        $this->reorder->addOrderReason($reason);

        $this->setUpNoErrors($formParams);
    }

    private function setUpSolvedNoErrors($formParams = [])
    {
        $this->orderOutOfStockRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->paymentMethodRepository
            ->method('findOneBy')
            ->willReturnCallback(function () {
                $method = new PaymentMethod();
                $method->setCode($this->reorder->getChannel()->getCode());

                return $method;
            });

        $this->weightingCalculation
            ->method('recalculate')
            ->willReturn(1000);

        $this->setUpNoErrors($formParams);
    }

    public function testProcess(): void
    {
        $this->setUpNoErrors();
        $this->processor->process($this->order, $this->reorder);

        $this->assertTrue($this->requestStack->getCurrentRequest()->get('bisComplete'));
    }

    public function testProcessCancelled(): void
    {
        $this->setUpCancelledNoErrors(['parentState' => OrderInterface::STATE_CANCELLED]);
        $this->processor->process($this->order, $this->reorder);

        $this->assertTrue($this->requestStack->getCurrentRequest()->get('bisComplete'));
    }

    public function testProcessSolved(): void
    {
        $this->setUpSolvedNoErrors([
            'parentState' => OrderOutOfStockInterface::STATE_SOLVED,
            'outOfStock' => OrderOutOfStockInterface::STATE_SOLVED,
        ]);

        $this->processor->process($this->order, $this->reorder);

        $this->assertTrue($this->requestStack->getCurrentRequest()->get('bisComplete'));
    }

    public function testProcessNoFormDataError(): void
    {
        $this->setUpNoErrors();
        $this->requestStack->getCurrentRequest()->query->remove('sylius_admin_order_creation_new_order');

        $this->expectException(InvalidArgumentException::class);
        $this->processor->process($this->order, $this->reorder);
    }

    public function testCancelParent(): void
    {
        $this->setUpCancelledNoErrors();

        $this->invokeMethod($this->processor, 'cancelParent', [$this->order, $this->reorder]);

        $this->assertEquals(OrderInterface::STATE_CANCELLED, $this->order->getState());

        foreach ($this->reorder->getOrderReason() as $orderReason) {
            $reasons = $this->order->getOrderReason()->filter(fn (OrderReason $reason): bool => $reason->getCreatedAt() === $orderReason->getCreatedAt());

            $this->assertNotEmpty($reasons);
        }
    }

    public function testCancelParentWrongSmFactoryError(): void
    {
        $this->smFactory
            ->method('get')
            ->willThrowException(new SMException());

        $this->setUpCancelledNoErrors();
        $this->expectException(SMException::class);

        $this->invokeMethod($this->processor, 'cancelParent', [$this->order, $this->reorder]);
    }

    public function testCancelParentCantApplySmError(): void
    {
        $this->smFactory
            ->method('get')
            ->willReturn($stateMachine = $this->createMock(StateMachineInterface::class));

        $stateMachine
            ->method('apply')
            ->willThrowException(new SMException());

        $this->setUpCancelledNoErrors();

        $this->expectException(SMException::class);

        $this->invokeMethod($this->processor, 'cancelParent', [$this->order, $this->reorder]);
    }

    public function testCancelParentOrderStateCancelled(): void
    {
        $this->order->setState(OrderInterface::STATE_CANCELLED);
        $this->setUpCancelledNoErrors();

        $this->invokeMethod($this->processor, 'cancelParent', [$this->order, $this->reorder]);

        $this->assertEquals(OrderInterface::STATE_CANCELLED, $this->order->getState());
    }

    public function testCancelParentOrderStateFullfilled(): void
    {
        $this->order->setState(OrderInterface::STATE_FULFILLED);
        $this->setUpCancelledNoErrors();

        $this->invokeMethod($this->processor, 'cancelParent', [$this->order, $this->reorder]);

        $this->assertEquals(OrderInterface::STATE_FULFILLED, $this->order->getState());
    }

    public function testCreateOutOfStockStatus(): void
    {
        $this->setUpSolvedNoErrors();
        $this->invokeMethod($this->processor, 'createOutOfStockStatus', [$this->order, $this->reorder, OrderOutOfStockInterface::STATE_PARTIALLY_PLANNED]);

        $this->assertNotNull($this->order->getOrderOutOfStock());
        $this->assertEquals(OrderOutOfStockInterface::STATE_SOLVED, $this->order->getOrderOutOfStock()->getStatus());
        $this->assertNotNull($this->reorder->getOrderOutOfStock());
        $this->assertEquals(OrderOutOfStockInterface::STATE_PARTIALLY_PLANNED, $this->reorder->getOrderOutOfStock()->getStatus());
        $this->assertInstanceOf(OrderVip::class, $this->reorder->getOrderVip());
        $this->assertEquals(OrderInterface::STATE_NEW, $this->reorder->getState());
        $this->assertEquals(OrderPaymentStates::STATE_AWAITING_PAYMENT, $this->reorder->getPaymentState());
        $this->assertEquals(OrderShippingStates::STATE_READY, $this->reorder->getShippingState());
        $this->assertEquals(OrderCheckoutStates::STATE_COMPLETED, $this->reorder->getCheckoutState());
        $this->assertNotNull($this->reorder->getCheckoutCompletedAt());

        $this->assertEquals(1, $this->reorder->getPayments()->count());

        /** @var Payment $payment */
        $payment = $this->reorder->getPayments()->first();

        $this->assertEquals($this->reorder->getCurrencyCode(), $payment->getCurrencyCode());
        $this->assertEquals(PaymentInterface::STATE_NEW, $payment->getState());
        $this->assertEquals($this->reorder->getChannel()->getCode(), $payment->getMethod()->getCode());

        $this->assertFalse($this->requestStack->getCurrentRequest()->get('toAddress'));
        $this->assertFalse($this->requestStack->getCurrentRequest()->get('toComplete'));

        $this->assertTrue($this->reorder->getStockPriority() >= 0);
    }

    public function testCreateOutOfStockStatusAlreadyHasOutOfStock(): void
    {
        $this->orderOutOfStockRepository
            ->method('findOneBy')
            ->willReturn(new OrderOutOfStock());

        $this->setUpSolvedNoErrors();
        $this->invokeMethod($this->processor, 'createOutOfStockStatus', [$this->order, $this->reorder, OrderOutOfStockInterface::STATE_PARTIALLY_PLANNED]);

        $this->assertEquals(true, true);
    }

    public function testCreateOutOfStockStatusOtherState(): void
    {
        $this->setUpSolvedNoErrors();
        $this->invokeMethod($this->processor, 'createOutOfStockStatus', [$this->order, $this->reorder, OrderOutOfStockInterface::STATE_SOLVED]);

        $this->assertEquals(OrderOutOfStockInterface::STATE_SOLVED, $this->reorder->getOrderOutOfStock()->getStatus());
    }

    public function testCreateOutOfStockStatusPaymentMethodError(): void
    {
        $this->paymentMethodRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->setUpSolvedNoErrors();

        $this->expectException(InvalidArgumentException::class);
        $this->invokeMethod($this->processor, 'createOutOfStockStatus', [$this->order, $this->reorder, OrderOutOfStockInterface::STATE_PARTIALLY_PLANNED]);
    }
}
