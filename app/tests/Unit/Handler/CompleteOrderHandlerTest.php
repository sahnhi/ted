<?php

namespace App\Tests\Unit\Handler;

use App\Business\Command\CompleteOrder;
use App\Doctrine\Entity\Sylius\Order;
use App\Handler\CompleteOrderHandler;
use App\Provider\CustomerProviderInterface;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SM\Factory\Factory;
use SM\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompleteOrderHandlerTest extends TestCase
{
    /** @var OrderRepositoryInterface|MockObject */
    private $orderRepository;
    /** @var ObjectManager|MockObject */
    private $orderManager;
    /** @var CustomerProviderInterface|MockObject */
    private $customerProvider;
    /** @var Factory|MockObject */
    private $stateMachineFactory;
    /** @var TranslatorInterface */
    private $translator;

    /** @var CompleteOrder */
    private $completeOrder;
    /** @var MockObject|StateMachineInterface */
    private $stateMachine;

    /** @var CompleteOrderHandler */
    private $completeOrderHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->orderManager = $this->createMock(ObjectManager::class);
        $this->orderManager->method('persist')
            ->willReturn(1);
        $this->customerProvider = $this->createMock(CustomerProviderInterface::class);
        $this->stateMachineFactory = $this->createMock(Factory::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->completeOrder = new CompleteOrder(
            'token',
            'test@tediber.com',
            new \DateTime(),
            new Request()
        );
    }

    protected function setUpDefault()
    {
        /** @var OrderInterface $order */
        $order = new Order();
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn($order);

        $this->stateMachine = $this->createMock(StateMachineInterface::class);
        $this->stateMachine->method('can')
            ->willReturn(true);
        $this->stateMachineFactory
            ->method('get')
            ->willReturn($this->stateMachine);

        $this->setUpHandler();
    }

    protected function setUpHandler()
    {
        $this->completeOrderHandler = new CompleteOrderHandler(
            $this->orderRepository,
            $this->orderManager,
            $this->customerProvider,
            $this->stateMachineFactory,
            $this->translator
        );
    }

    /**
     * @throws \SM\SMException
     */
    public function testHandleWithFrontData(): void
    {
        $this->setUpDefault();

        $request = new Request([], ['version' => 'version', 'user-agent' => 'user-agent']);
        $this->completeOrder = new CompleteOrder(
            'token',
            'test@tediber.com',
            new \DateTime(),
            $request
        );

        /** @var Order $order */
        $order = $this->completeOrderHandler->handle($this->completeOrder);
        $this->assertNotEmpty($order->getFrontData());
    }

    /**
     * @throws \SM\SMException
     */
    public function testHandleWithoutFrontData(): void
    {
        $this->setUpDefault();

        /** @var Order $order */
        $order = $this->completeOrderHandler->handle($this->completeOrder);
        $this->assertEmpty($order->getFrontData());
    }

    /**
     * @throws \SM\SMException
     */
    public function testHandleOrderNotFound(): void
    {
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn(null);
        $this->setUpHandler();

        $this->expectException(\InvalidArgumentException::class);
        $this->completeOrderHandler->handle($this->completeOrder);
    }

    /**
     * @throws \SM\SMException
     */
    public function testHandleStateMachineCannot(): void
    {
        $this->stateMachine = $this->createMock(StateMachineInterface::class);
        $this->stateMachine
            ->method('can')
            ->willReturn(false);
        $this->stateMachineFactory
            ->method('get')
            ->willReturn($this->stateMachine);
        $this->setUpHandler();

        $this->expectException(\InvalidArgumentException::class);
        $this->completeOrderHandler->handle($this->completeOrder);
    }
}
