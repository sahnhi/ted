<?php

namespace App\Tests\Unit\Controller\Admin\Action;

use App\Controller\Admin\Action\BisCreationWithExchangeAction;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Factory\OrderFactoryInterface;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Controller\EventDispatcherInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\InvalidArgumentException;

class BisCreationWithExchangeActionTest extends TestCase
{
    private Order $order;
    private RequestConfiguration $requestConfiguration;
    private Request $request;
    private BisCreationWithExchangeAction $action;
    private TranslatorInterface $translator;
    private EntityManagerInterface $orderManager;
    private OrderFactoryInterface $orderFactory;
    private EventDispatcherInterface $eventDispatcher;
    private readonly OrderProcessorInterface $orderProcessor;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->translator = $this->getTranslator();
        $this->orderManager = $this->createMock(EntityManagerInterface::class);
        $this->orderFactory = $this->createMock(OrderFactoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->requestConfiguration = $this->createMock(RequestConfiguration::class);
        $this->request = new Request();
        $this->orderProcessor = $this->createMock(OrderProcessorInterface::class);
    }

    public function setUpAction()
    {
        $this->action = new BisCreationWithExchangeAction(
            $this->translator,
            $this->orderManager,
            $this->orderFactory,
            $this->eventDispatcher,
            $this->orderProcessor,
        );
    }

    private function setUpNoErrors()
    {
        $this->requestConfiguration
            ->method('getRequest')
            ->willReturn($this->request);

        $method = new ShippingMethod();
        $shipment = new Shipment();

        $shipment->setMethod($method);
        $this->order->addShipment($shipment);

        $this->eventDispatcher
            ->method('dispatchPreEvent')
            ->willReturnCallback(function () {
                $event = $this->createMock(ResourceControllerEvent::class);
                $event->method('isStopped')->willReturn(false);

                return $event;
            });

        $this->eventDispatcher
            ->method('dispatchPostEvent')
            ->willReturnCallback(function () {
                $event = $this->createMock(ResourceControllerEvent::class);
                $event->method('getResponse')->willReturn(null);

                return $event;
            });

        $this->setUpAction();
    }

    public function testInvoke(): void
    {
        $this->setUpNoErrors();
        $response = $this->action->__invoke($this->requestConfiguration, $this->order);

        $this->assertEquals(BisCreationWithExchangeAction::BIS_TYPE_EXCHANGE, $this->request->get('bisType'));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }

    public function testInvokePoste(): void
    {
        $this->setUpNoErrors();
        $this->order->getShipments()->first()->getMethod()->setCode(ShippingMethodInterface::METHOD_POSTE);

        $response = $this->action->__invoke($this->requestConfiguration, $this->order);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals($this->translator->trans('sylius_admin_order_creation.ui.bis_exchange_create_error'), $response->getContent());
    }

    public function testInvokeEventStoppedAndIsHtml(): void
    {
        $this->eventDispatcher
            ->method('dispatchPreEvent')
            ->willReturnCallback(function () {
                $event = $this->createMock(ResourceControllerEvent::class);
                $event->method('isStopped')->willReturn(true);

                return $event;
            });

        $this->requestConfiguration
            ->method('isHtmlRequest')
            ->willReturn(true);

        $this->setUpNoErrors();
        $response = $this->action->__invoke($this->requestConfiguration, $this->order);

        $this->assertEquals(BisCreationWithExchangeAction::BIS_TYPE_EXCHANGE, $this->request->get('bisType'));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
    }

    public function testInvokeEventStoppedAndIsNotHtml(): void
    {
        $this->eventDispatcher
            ->method('dispatchPreEvent')
            ->willReturnCallback(function () {
                $event = $this->createMock(ResourceControllerEvent::class);
                $event->method('isStopped')->willReturn(true);

                return $event;
            });

        $this->setUpNoErrors();
        $response = $this->action->__invoke($this->requestConfiguration, $this->order);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals($this->translator->trans('sylius_admin_order_creation.ui.bis_exchange_create_error'), $response->getContent());
    }

    public function testInvokePostEventNotNull(): void
    {
        $this->eventDispatcher
            ->method('dispatchPostEvent')
            ->willReturnCallback(function () {
                $event = $this->createMock(ResourceControllerEvent::class);
                $event->method('getResponse')->willReturn(new Response());

                return $event;
            });

        $this->setUpNoErrors();
        $response = $this->action->__invoke($this->requestConfiguration, $this->order);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals($this->translator->trans('sylius_admin_order_creation.ui.bis_exchange_create_error'), $response->getContent());
    }

    public function testInvokeShipmentNull(): void
    {
        $this->setUpNoErrors();
        $this->order->getShipments()->clear();

        $this->expectException(InvalidArgumentException::class);
        $this->action->__invoke($this->requestConfiguration, $this->order);
    }

    public function testInvokeMethodNull(): void
    {
        $this->setUpNoErrors();
        $this->order->getShipments()->first()->setMethod(null);

        $this->expectException(InvalidArgumentException::class);
        $this->action->__invoke($this->requestConfiguration, $this->order);
    }
}
