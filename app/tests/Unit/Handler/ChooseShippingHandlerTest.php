<?php

declare(strict_types=1);

namespace App\Tests\Unit\Handler;

use App\Api\Odoo\Message\Return\CreateReturn;
use App\Business\Command\ChooseShipping;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Handler\ChooseShippingHandler;
use App\Provider\Shipping\EligibleShippingMethodProvider;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use App\Services\Channel\ChannelCode;
use App\Services\Shipment\ShipmentService;
use App\Services\ShippingMethod\ShippingMethodService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use SM\Factory\FactoryInterface;
use SM\SMException;
use SM\StateMachine\StateMachine;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChooseShippingHandlerTest extends TestCase
{
    private readonly OrderRepository $orderRepository;

    private readonly FactoryInterface $stateMachineFactory;

    private readonly TranslatorInterface $translator;

    private readonly MessageBusInterface $messageBus;

    private readonly ShippingMethodCategoryProvider $shippingMethodCategoryProvider;

    private readonly EligibleShippingMethodProvider $eligibleShippingMethodProvider;

    private ChooseShippingHandler $handler;

    public function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->stateMachineFactory = $this->createMock(FactoryInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->shippingMethodCategoryProvider = $this->createMock(ShippingMethodCategoryProvider::class);
        $this->eligibleShippingMethodProvider = $this->createMock(EligibleShippingMethodProvider::class);

        $this->handler = new ChooseShippingHandler(
            $this->orderRepository,
            $this->stateMachineFactory,
            $this->translator,
            new ShipmentService($this->createMock(ShippingMethodService::class)),
            $this->messageBus,
            $this->shippingMethodCategoryProvider,
            $this->eligibleShippingMethodProvider
        );
    }

    /**
     * @throws SMException
     */
    public function testHandleOrderWithSelectedShippingMethodCategory(): void
    {
        $message = new ChooseShipping();
        $message
            ->setMethod('selected-method')
            ->setOrderToken('example-token-value');

        $channel = new Channel();

        $order = new Order();
        $order->setChannel($channel);

        $selectedShippingMethodCategory = new ShippingMethodCategory();

        $firstShipment = new Shipment();
        $firstShipment->setShippingMethodCategory($selectedShippingMethodCategory);
        $order->addShipment($firstShipment);

        $secondShipment = new Shipment();
        $secondShipment->setShippingMethodCategory($selectedShippingMethodCategory);
        $order->addShipment($secondShipment);

        $this->orderRepository
            ->expects($this->once())
            ->method('findCartByTokenValue')
            ->with('example-token-value')
            ->willReturn($order);

        $eligibleShippingMethod = new ShippingMethod();
        $eligibleShippingMethod->setCode('selected-method');
        $eligibleShippingMethods = new ArrayCollection([$eligibleShippingMethod]);

        $this->eligibleShippingMethodProvider
            ->expects($this->once())
            ->method('provideEligibleShippingMethods')
            ->with($selectedShippingMethodCategory, $order->getShipments(), $channel)
            ->willReturn($eligibleShippingMethods);

        $stateMachine = $this->createMock(StateMachine::class);
        $this->stateMachineFactory
            ->expects($this->once())
            ->method('get')
            ->with($order, OrderCheckoutTransitions::GRAPH)
            ->willReturn($stateMachine);

        $stateMachine
            ->expects($this->once())
            ->method('can')
            ->with(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)
            ->willReturn(true);

        $stateMachine
            ->expects($this->once())
            ->method('apply')
            ->with(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING);

        $this->messageBus
            ->expects($this->never())
            ->method('dispatch');

        $this->handler->handle($message);

        self::assertCount(2, $order->getShipments());

        /** @var Shipment $shipment */
        foreach ($order->getShipments() as $shipment) {
            self::assertSame($eligibleShippingMethod, $shipment->getMethod());
            self::assertSame($message->getStartDeliverySlot(), $shipment->getStartDeliverySlot());
            self::assertSame($message->getEndDeliverySlot(), $shipment->getEndDeliverySlot());
            self::assertSame($message->getIdShippingMethodBalou(), $shipment->getIdShippingMethodBalou());
        }
    }

    public function testHandleOrderWithNoSelectedShippingMethodCategory(): void
    {
        $message = new ChooseShipping();
        $message
            ->setMethod('selected-method')
            ->setOrderToken('example-token-value');

        $channel = new Channel();
        $channel->setCode(ChannelCode::CHANNEL_RETURN);

        $order = $this->createMock(Order::class);

        $order
            ->expects($this->once())
            ->method('getId')
            ->willReturn(42);

        $order
            ->expects($this->exactly(2))
            ->method('getChannel')
            ->willReturn($channel);

        $order
            ->expects($this->once())
            ->method('getShippingMethodCategories')
            ->willReturn([]);

        $order
            ->expects($this->once())
            ->method('getReturnType')
            ->willReturn(Reason::RETURN_TYPE_AUTONOMOUS);

        $firstShipment = new Shipment();
        $order->addShipment($firstShipment);

        $secondShipment = new Shipment();
        $order->addShipment($secondShipment);

        $order
            ->method('getShipments')
            ->willReturn(new ArrayCollection([$firstShipment, $secondShipment]));

        $this->orderRepository
            ->expects($this->once())
            ->method('findCartByTokenValue')
            ->with('example-token-value')
            ->willReturn($order);

        $eligibleShippingMethodCategory = new ShippingMethodCategory();
        $this->shippingMethodCategoryProvider
            ->expects($this->once())
            ->method('provideEligibleForOrder')
            ->with($order)
            ->willReturn([$eligibleShippingMethodCategory]);

        $eligibleShippingMethod = new ShippingMethod();
        $eligibleShippingMethod->setCode('selected-method');
        $eligibleShippingMethods = new ArrayCollection([$eligibleShippingMethod]);

        $this->eligibleShippingMethodProvider
            ->expects($this->once())
            ->method('provideEligibleShippingMethods')
            ->with($eligibleShippingMethodCategory, $order->getShipments(), $channel)
            ->willReturn($eligibleShippingMethods);

        $stateMachine = $this->createMock(StateMachine::class);
        $this->stateMachineFactory
            ->expects($this->once())
            ->method('get')
            ->with($order, OrderCheckoutTransitions::GRAPH)
            ->willReturn($stateMachine);

        $stateMachine
            ->expects($this->once())
            ->method('can')
            ->with(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)
            ->willReturn(true);

        $stateMachine
            ->expects($this->once())
            ->method('apply')
            ->with(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING);

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with(new CreateReturn(42))
            // See https://github.com/symfony/symfony/issues/33740
            ->willReturn(new Envelope(new \stdClass()));

        $this->handler->handle($message);

        self::assertCount(2, $order->getShipments());

        /** @var Shipment $shipment */
        foreach ($order->getShipments() as $shipment) {
            self::assertSame($eligibleShippingMethod, $shipment->getMethod());
            self::assertSame($message->getStartDeliverySlot(), $shipment->getStartDeliverySlot());
            self::assertSame($message->getEndDeliverySlot(), $shipment->getEndDeliverySlot());
            self::assertSame($message->getIdShippingMethodBalou(), $shipment->getIdShippingMethodBalou());
        }
    }

    /**
     * @throws SMException
     */
    public function testHandleNoCart(): void
    {
        $message = new ChooseShipping();
        $message->setOrderToken('invalid-token-value');

        $this->orderRepository
            ->expects($this->once())
            ->method('findCartByTokenValue')
            ->with('invalid-token-value')
            ->willReturn(null);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('app.api.order.shipping.error.cart_not_found')
            ->willReturn("Le panier n'a pas été trouvé.");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le panier n'a pas été trouvé.");

        $this->handler->handle($message);
    }

    /**
     * @throws SMException
     */
    public function testHandleMethodNotFound(): void
    {
        $message = new ChooseShipping();
        $message
            ->setMethod('unknown-method')
            ->setOrderToken('example-token-value');

        $channel = new Channel();

        $order = new Order();
        $order->setChannel($channel);

        $selectedShippingMethodCategory = new ShippingMethodCategory();

        $shipment = new Shipment();
        $shipment->setShippingMethodCategory($selectedShippingMethodCategory);
        $order->addShipment($shipment);

        $this->orderRepository
            ->expects($this->once())
            ->method('findCartByTokenValue')
            ->with('example-token-value')
            ->willReturn($order);

        $eligibleShippingMethod = new ShippingMethod();
        $eligibleShippingMethod->setCode('eligible-method');
        $eligibleShippingMethods = new ArrayCollection([$eligibleShippingMethod]);

        $this->eligibleShippingMethodProvider
            ->expects($this->once())
            ->method('provideEligibleShippingMethods')
            ->with($selectedShippingMethodCategory, $order->getShipments(), $channel)
            ->willReturn($eligibleShippingMethods);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('app.api.order.shipping.error.shipment_not_eligible')
            ->willReturn("Cette livraison n'est pas éligible pour le mode de livraison choisi.");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cette livraison n'est pas éligible pour le mode de livraison choisi.");

        $this->handler->handle($message);
    }
}
