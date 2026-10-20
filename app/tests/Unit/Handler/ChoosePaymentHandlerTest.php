<?php

namespace App\Tests\Unit\Handler;

use App\Business\Command\ChoosePayment;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PaymentMethod;
use App\Handler\ChoosePaymentHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SM\Factory\FactoryInterface;
use SM\SMException;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Payment\Provider\OrderPaymentProviderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface as PaymentInterfaceAlias;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\InvalidArgumentException;

class ChoosePaymentHandlerTest extends TestCase
{
    /** @var MockObject|StateMachineInterface */
    private $stateMachine;
    /** @var Order */
    private $order;
    /** @var PaymentMethod */
    private $paymentMeghod;
    /** @var ChoosePayment */
    private $choosePayment;

    /** @var MockObject|OrderRepositoryInterface */
    private $orderRepository;
    /** @var MockObject|PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;
    /** @var MockObject|FactoryInterface */
    private $stateMachineFactory;
    /** @var MockObject|OrderPaymentProviderInterface */
    private $orderPaymentProvider;
    /** @var MockObject|TranslatorInterface */
    private $translator;

    /** @var ChoosePaymentHandler */
    private $handler;

    public function setUp(): void
    {
        parent::setUp();

        $this->stateMachine = $this->createMock(StateMachineInterface::class);
        $this->order = new Order();
        $this->order->setTokenValue('token');
        $this->paymentMeghod = new PaymentMethod();
        $this->paymentMeghod->setCode('code');
        $this->paymentMeghod->setCode('code');
        $this->choosePayment = new ChoosePayment($this->order->getTokenValue(), $this->paymentMeghod->getCode());

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->paymentMethodRepository = $this->createMock(PaymentMethodRepositoryInterface::class);
        $this->stateMachineFactory = $this->createMock(FactoryInterface::class);
        $this->stateMachineFactory
            ->method('get')
            ->willReturn($this->stateMachine);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->orderPaymentProvider = $this->createMock(OrderPaymentProviderInterface::class);
    }

    private function setUpHandler()
    {
        $this->handler = new ChoosePaymentHandler(
            $this->orderRepository,
            $this->paymentMethodRepository,
            $this->stateMachineFactory,
            $this->translator,
            $this->orderPaymentProvider
        );
    }

    /**
     * @throws SMException
     */
    public function testHandle(): void
    {
        $payment = new Payment();
        $payment->setState(PaymentInterfaceAlias::STATE_CART);
        $this->order->addPayment($payment);
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn($this->order);
        $this->stateMachine
            ->method('can')
            ->willReturn(true);
        $this->paymentMethodRepository
            ->method('findOneBy')
            ->willReturn($this->paymentMeghod);
        $this->setUpHandler();

        $payment = $this->handler->handle($this->choosePayment);

        $this->assertEquals($this->paymentMeghod, $payment->getMethod());
        $this->assertIsArray($payment->getDetails());
        $this->assertEmpty($payment->getDetails());
    }

    /**
     * @throws SMException
     */
    public function testHandleHasNewPayment(): void
    {
        $payment = new Payment();
        $payment->setState(PaymentInterfaceAlias::STATE_NEW);
        $paymentCart = new Payment();
        $payment->setState(PaymentInterfaceAlias::STATE_CART);
        $this->order->addPayment($payment);
        $this->order->addPayment($paymentCart);
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn($this->order);
        $this->stateMachine
            ->method('can')
            ->willReturn(true);
        $this->paymentMethodRepository
            ->method('findOneBy')
            ->willReturn($this->paymentMeghod);
        $this->setUpHandler();

        $paymentResult = $this->handler->handle($this->choosePayment);

        $this->assertNotEquals($payment, $paymentResult);
        $this->assertEquals($paymentCart, $paymentResult);
    }

    /**
     * @throws SMException
     */
    public function testHandleOrderNotFound(): void
    {
        $this->setUpHandler();

        $this->expectException(InvalidArgumentException::class);
        $payment = $this->handler->handle($this->choosePayment);
    }

    /**
     * @throws SMException
     */
    public function testHandleCompletedPayment(): void
    {
        $payment = new Payment();
        $payment->setState(PaymentInterfaceAlias::STATE_COMPLETED);
        $this->order->addPayment($payment);
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn($this->order);
        $this->setUpHandler();

        $this->expectException(InvalidArgumentException::class);
        $payment = $this->handler->handle($this->choosePayment);
    }

    /**
     * @throws SMException
     */
    public function testHandleProcessingPayment(): void
    {
        $payment = new Payment();
        $payment->setState(PaymentInterfaceAlias::STATE_PROCESSING);
        $this->order->addPayment($payment);
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn($this->order);
        $this->setUpHandler();

        $this->expectException(InvalidArgumentException::class);
        $payment = $this->handler->handle($this->choosePayment);
    }

    /**
     * @throws SMException
     */
    public function testHandleRefundedPayment(): void
    {
        $payment = new Payment();
        $payment->setState(PaymentInterfaceAlias::STATE_PROCESSING);
        $this->order->addPayment($payment);
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn($this->order);
        $this->setUpHandler();

        $this->expectException(InvalidArgumentException::class);
        $payment = $this->handler->handle($this->choosePayment);
    }

    /**
     * @throws SMException
     */
    public function testHandleNoPaymentMethod(): void
    {
        $payment = new Payment();
        $payment->setState(PaymentInterfaceAlias::STATE_CART);
        $this->order->addPayment($payment);
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn($this->order);
        $this->stateMachine
            ->method('can')
            ->willReturn(true);
        $this->setUpHandler();

        $this->expectException(InvalidArgumentException::class);
        $payment = $this->handler->handle($this->choosePayment);
    }
}
