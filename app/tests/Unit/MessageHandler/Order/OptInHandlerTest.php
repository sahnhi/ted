<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler\Order;

use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Exception\OrderNotFound;
use App\MessageHandler\Order\OrderOptInHandler;
use App\Model\Order\OrderOptIn;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachine;
use Sylius\Bundle\ApiBundle\Provider\CustomerProviderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;

class OptInHandlerTest extends TestCase
{
    private readonly OrderRepositoryInterface $orderRepository;

    private readonly CustomerProviderInterface $customerProvider;

    private readonly EntityManagerInterface $entityManager;

    private readonly FactoryInterface $stateMachineFactory;

    private readonly OrderOptInHandler $handler;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->customerProvider = $this->createMock(CustomerProviderInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->stateMachineFactory = $this->createMock(FactoryInterface::class);

        $this->handler = new OrderOptInHandler(
            $this->orderRepository,
            $this->customerProvider,
            $this->entityManager,
            $this->stateMachineFactory,
        );
    }

    public function testItThrowsAnInvalidArgumentExceptionIfTheOrderIsNotFoundForTheGivenToken(): void
    {
        $this->orderRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['tokenValue' => 'random-token'])
            ->willReturn(null);

        $this->customerProvider
            ->expects($this->never())
            ->method('provide')
            ->with('foo@bar.com');

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->stateMachineFactory
            ->expects($this->never())
            ->method('get');

        $this->expectException(OrderNotFound::class);
        $this->expectExceptionMessage('Order with token value "random-token" not found.');

        $this->handler->__invoke(new OrderOptIn('random-token', 'foo@bar.com'));
    }

    public function testItCreateACustomerThenSetsTheOptInIfTheOrderHasNoCustomer(): void
    {
        $order = new Order();

        $this->orderRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['tokenValue' => 'random-token'])
            ->willReturn($order);

        $customer = new Customer();
        $this->customerProvider
            ->expects($this->once())
            ->method('provide')
            ->with('foo@bar.com')
            ->willReturn($customer);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($customer);

        $stateMachine = $this->createMock(StateMachine::class);
        $this->stateMachineFactory
            ->expects($this->once())
            ->method('get')
            ->with($order, OrderCheckoutTransitions::GRAPH)
            ->willReturn($stateMachine);

        $stateMachine
            ->expects($this->once())
            ->method('can')
            ->with(OrderCheckoutTransitions::TRANSITION_ADDRESS)
            ->willReturn(true);

        $stateMachine
            ->expects($this->once())
            ->method('apply')
            ->with(OrderCheckoutTransitions::TRANSITION_ADDRESS);

        $order = $this->handler->__invoke(new OrderOptIn('random-token', 'foo@bar.com'));

        self::assertSame($customer, $order->getCustomer());
        self::assertTrue($customer->getOptin());
        self::assertTrue($customer->isSubscribedToNewsletter());
    }

    public function testItCreateACustomerThenSetsTheOptInIfTheOrderHasACustomer(): void
    {
        $customer = new Customer();
        $order = new Order();
        $order->setCustomer($customer);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['tokenValue' => 'random-token'])
            ->willReturn($order);

        $this->customerProvider
            ->expects($this->never())
            ->method('provide');

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $stateMachine = $this->createMock(StateMachine::class);
        $this->stateMachineFactory
            ->expects($this->once())
            ->method('get')
            ->with($order, OrderCheckoutTransitions::GRAPH)
            ->willReturn($stateMachine);

        $stateMachine
            ->expects($this->once())
            ->method('can')
            ->with(OrderCheckoutTransitions::TRANSITION_ADDRESS)
            ->willReturn(true);

        $stateMachine
            ->expects($this->once())
            ->method('apply')
            ->with(OrderCheckoutTransitions::TRANSITION_ADDRESS);

        $order = $this->handler->__invoke(new OrderOptIn('random-token', 'foo@bar.com'));

        self::assertSame($customer, $order->getCustomer());
        self::assertTrue($customer->getOptin());
        self::assertTrue($customer->isSubscribedToNewsletter());
    }
}
