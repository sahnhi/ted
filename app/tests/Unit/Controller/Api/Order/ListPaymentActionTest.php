<?php

namespace App\Tests\Unit\Controller\Api\Order;

use App\Checker\PaymentMethodEligibleChecker;
use App\Controller\Api\Order\ListPaymentAction;
use App\Doctrine\Entity\Sylius\PaymentMethod;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Factory\View\PaymentMethodViewFactoryInterface;
use App\Tests\TestCase;
use FOS\RestBundle\View\ViewHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;

class ListPaymentActionTest extends TestCase
{
    /** @var MockObject|PaymentMethodsResolverInterface */
    private $paymentMethodsResolver;
    /** @var MockObject|PaymentMethodEligibleChecker */
    private $paymentMethodEligibleChecker;

    /** @var ListPaymentAction */
    private $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->paymentMethodsResolver = $this->createMock(PaymentMethodsResolverInterface::class);
        $this->paymentMethodEligibleChecker = $this->createMock(PaymentMethodEligibleChecker::class);
    }

    private function setUpController()
    {
        $this->controller = new ListPaymentAction(
            $this->createMock(OrderRepository::class),
            $this->paymentMethodsResolver,
            $this->createMock(PaymentMethodViewFactoryInterface::class),
            $this->createMock(ViewHandlerInterface::class),
            $this->paymentMethodEligibleChecker
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetPaymentMethods(): void
    {
        $paymentMethod = new PaymentMethod();
        $paymentMethod->setCode('code');
        $this->paymentMethodsResolver
            ->method('getSupportedMethods')
            ->willReturn([$paymentMethod]);
        $this->paymentMethodEligibleChecker
            ->method('isPaymentEligible')
            ->willReturn(true);
        $this->setUpController();

        $methods = $this->invokeMethod(
            $this->controller,
            'getPaymentMethods',
            [new Payment(), 100, 'en_GB']
        );

        $this->assertIsArray($methods);
        $this->assertNotEmpty($methods);
        $this->assertArrayHasKey('methods', $methods);
        $this->assertArrayHasKey($paymentMethod->getCode(), $methods['methods']);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetPaymentMethodsNoSupportedMethod(): void
    {
        $this->setUpController();

        $methods = $this->invokeMethod(
            $this->controller,
            'getPaymentMethods',
            [new Payment(), 100, 'en_GB']
        );

        $this->assertEmpty($methods);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetPaymentMethodsNoEligibleMethod(): void
    {
        $this->paymentMethodsResolver
            ->method('getSupportedMethods')
            ->willReturn([new PaymentMethod()]);
        $this->paymentMethodEligibleChecker
            ->method('isPaymentEligible')
            ->willReturn(false);
        $this->setUpController();

        $methods = $this->invokeMethod(
            $this->controller,
            'getPaymentMethods',
            [new Payment(), 100, 'en_GB']
        );

        $this->assertEmpty($methods);
    }
}
