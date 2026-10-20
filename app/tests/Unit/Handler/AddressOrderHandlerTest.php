<?php

namespace App\Tests\Unit\Handler;

use App\Business\Command\AddressOrder;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\CustomerRepository;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Handler\AddressOrderHandler;
use App\Model\Address;
use App\Model\Customer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SM\StateMachine\StateMachine;
use Sylius\Component\Core\Factory\AddressFactory;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\InvalidArgumentException;

class AddressOrderHandlerTest extends TestCase
{
    public const LOCALE = 'en_GB';

    /** @var OrderRepository|MockObject */
    private $orderRepository;
    /** @var CustomerRepository|MockObject */
    private $customerRepository;
    /** @var MockObject|AddressFactory */
    private $addressFactory;
    /** @var MockObject|Factory */
    private $customerFactory;
    /** @var MockObject|\SM\Factory\Factory */
    private $stateMachineFactory;
    /** @var MockObject|ValidatorInterface */
    private $validator;
    /** @var MockObject|TranslatorInterface */
    private $translator;

    /** @var AddressOrder */
    private $addressOrder;
    /** @var Order */
    private $order;
    /** @var MockObject|StateMachine */
    private $stateMachine;

    /** @var AddressOrderHandler */
    private $handler;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->order->setLocaleCode(self::LOCALE);
        $this->stateMachine = $this->createMock(StateMachine::class);

        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->customerRepository = $this->createMock(CustomerRepository::class);
        $this->addressFactory = $this->createMock(AddressFactory::class);
        $this->addressFactory
            ->method('createNew')
            ->willReturn(new \App\Doctrine\Entity\Sylius\Address(), new \App\Doctrine\Entity\Sylius\Address());
        $this->customerFactory = new Factory(\App\Doctrine\Entity\Sylius\Customer::class);
        $this->stateMachineFactory = $this->createMock(\SM\Factory\Factory::class);
        $this->stateMachineFactory
            ->method('get')
            ->willReturn($this->stateMachine);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->addressOrder = new AddressOrder(
            'token',
            Address::createFromArray([
                'firstName' => 'shipping',
                'lastName' => 'shipping',
                'city' => 'shipping',
                'street' => 'shipping',
                'countryCode' => 'shipping',
                'postcode' => 'shipping',
                'details' => 'shipping',
                'tva' => 'shipping',
            ]),
            Address::createFromArray([
                'firstName' => 'billing',
                'lastName' => 'billing',
                'city' => 'billing',
                'street' => 'billing',
                'countryCode' => 'billing',
                'postcode' => 'billing',
                'tva' => 'billing',
            ]),
            Customer::createFromArray(['email' => 'customer@test.com', 'optin' => true]),
            $this->order
        );
    }

    public function setUpHandler()
    {
        $this->handler = new AddressOrderHandler(
            $this->customerRepository,
            $this->addressFactory,
            $this->customerFactory,
            $this->stateMachineFactory,
            $this->validator,
            $this->translator
        );
    }

    private function setUpNoErrors()
    {
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn($this->order);
        $this->stateMachine
            ->method('can')
            ->willReturn(true);
        $this->validator
            ->method('validate')
            ->willReturn([]);

        $this->setUpHandler();
    }

    /**
     * @throws \SM\SMException
     */
    public function testHandle(): void
    {
        $this->setUpNoErrors();

        /** @var Order $order */
        $order = $this->handler->handle($this->addressOrder);

        $this->assertEquals($this->addressOrder->customer()->email(), $order->getCustomer()->getEmail());
        $this->assertEquals($this->addressOrder->shippingAddress()->getFirstName(), $order->getShippingAddress()->getFirstName());
        $this->assertEquals($this->addressOrder->billingAddress()->getFirstName(), $order->getBillingAddress()->getFirstName());
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

        $this->expectException(InvalidArgumentException::class);
        $order = $this->handler->handle($this->addressOrder);
    }

    /**
     * @throws \SM\SMException
     */
    public function testHandleOrderCannotComplete(): void
    {
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn($this->order);
        $this->stateMachine
            ->method('can')
            ->willReturn(false);

        $this->setUpHandler();

        $this->expectException(InvalidArgumentException::class);
        $order = $this->handler->handle($this->addressOrder);
    }

    /**
     * @throws \SM\SMException
     */
    public function testHandleOrderInvalidEmail(): void
    {
        $this->orderRepository
            ->method('findOneBy')
            ->willReturn($this->order);
        $this->stateMachine
            ->method('can')
            ->willReturn(true);
        $this->validator
            ->method('validate')
            ->willReturn(['error']);
        $this->setUpHandler();

        $this->expectException(InvalidArgumentException::class);
        $order = $this->handler->handle($this->addressOrder);
    }
}
