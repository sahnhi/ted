<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PaymentMethod;
use App\Doctrine\Repository\Sylius\PaymentRepositoryInterface;
use App\ReorderProcessing\BisPaymentProcessor;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Factory\PaymentFactoryInterface;
use Webmozart\Assert\InvalidArgumentException;

class BisPaymentProcessorTest extends TestCase
{
    /** @var Order */
    private $order;

    /** @var Order */
    private $reorder;

    /** @var MockObject|PaymentFactoryInterface */
    private $paymentFactory;

    /** @var MockObject|PaymentRepositoryInterface */
    private $paymentMethodRepository;

    /** @var BisPaymentProcessor */
    private $processor;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
        $this->paymentFactory = $this->createMock(PaymentFactoryInterface::class);
        $this->paymentMethodRepository = $this->createMock(PaymentMethodRepositoryInterface::class);

        $this->order->setCurrencyCode('EUR');
    }

    public function setUpProcessor()
    {
        $this->processor = new BisPaymentProcessor(
            $this->paymentFactory,
            $this->paymentMethodRepository
        );
    }

    private function setUpNoErrors()
    {
        $this->paymentMethodRepository
            ->method('findOneBy')
            ->willReturnCallback(function () {
                $paymentMethod = new PaymentMethod();
                $paymentMethod->setCode('bis');

                return $paymentMethod;
            });

        $this->paymentFactory
            ->method('createNew')
            ->willReturn(new Payment());

        $this->setUpProcessor();
    }

    public function testProcess(): void
    {
        $this->setUpNoErrors();
        $this->processor->process($this->order, $this->reorder);

        $this->assertEquals(1, $this->reorder->getPayments()->count());
        $payment = $this->reorder->getPayments()->first();

        $this->assertEquals('bis', $payment->getMethod()->getCode());
        $this->assertEquals(PaymentInterface::STATE_COMPLETED, $payment->getState());
        $this->assertEquals($this->order->getCurrencyCode(), $payment->getCurrencyCode());
    }

    public function testProcessNoMethodError(): void
    {
        $this->paymentMethodRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->setUpNoErrors();
        $this->expectException(InvalidArgumentException::class);

        $this->processor->process($this->order, $this->reorder);
    }
}
