<?php

namespace App\Tests\Unit\Controller\Admin;

use App\Controller\Admin\OrderController;
use App\Doctrine\Entity\CustomerSupport;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Services\Admin\Export\ExportCsv;
use App\Services\Admin\OrderService;
use App\Services\Mail\OutOfStock\SendEmailToCustomer;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\ResourceBundle\Controller\AuthorizationCheckerInterface;
use Sylius\Bundle\ResourceBundle\Controller\EventDispatcherInterface;
use Sylius\Bundle\ResourceBundle\Controller\FlashHelperInterface;
use Sylius\Bundle\ResourceBundle\Controller\NewResourceFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\RedirectHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceDeleteHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceFormFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourcesCollectionProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceUpdateHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\SingleResourceProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\StateMachineInterface;
use Sylius\Bundle\ResourceBundle\Controller\ViewHandlerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zendesk\API\HttpClient;
use Zendesk\API\Resources\Core\Tickets;

class OrderControllerTest extends TestCase
{
    /** @var MetadataInterface|ObjectProphecy */
    protected $metadata;
    /** @var RequestConfigurationFactoryInterface|ObjectProphecy */
    protected $requestConfigurationFactory;
    /** @var ViewHandlerInterface|ObjectProphecy */
    protected $viewHandler;
    /** @var RepositoryInterface|ObjectProphecy */
    protected $repository;
    /** @var FactoryInterface|ObjectProphecy */
    protected $factory;
    /** @var NewResourceFactoryInterface|ObjectProphecy */
    protected $newResourceFactory;
    /** @var ObjectManager|ObjectProphecy */
    protected $manager;
    /** @var SingleResourceProviderInterface|ObjectProphecy */
    protected $singleResourceProvider;
    /** @var ResourcesCollectionProviderInterface|ObjectProphecy */
    protected $resourcesFinder;
    /** @var ResourceFormFactoryInterface|ObjectProphecy */
    protected $resourceFormFactory;
    /** @var RedirectHandlerInterface|ObjectProphecy */
    protected $redirectHandler;
    /** @var FlashHelperInterface|ObjectProphecy */
    protected $flashHelper;
    /** @var AuthorizationCheckerInterface|ObjectProphecy */
    protected $authorizationChecker;
    /** @var EventDispatcherInterface|ObjectProphecy */
    protected $eventDispatcher;
    /** @var StateMachineInterface|ObjectProphecy */
    protected $stateMachine;
    /** @var ResourceUpdateHandlerInterface|ObjectProphecy */
    protected $resourceUpdateHandler;
    /** @var ResourceDeleteHandlerInterface|ObjectProphecy */
    protected $resourceDeleteHandler;
    /** @var ContainerInterface|ObjectProphecy */
    protected $container;
    /** @var ManagerRegistry|ObjectProphecy */
    protected $doctrine;
    /** @var ObjectManager|ObjectProphecy */
    protected $objectManager;
    /** @var OrderInterface|ObjectProphecy */
    protected $resource;
    /** @var Customer|ObjectProphecy */
    protected $customer;
    /** @var HttpClient|ObjectProphecy */
    protected $zendeskClient;
    /** @var Tickets|ObjectProphecy */
    protected $tickets;
    /** @var OrderService|ObjectProphecy */
    private $orderService;
    /** @var ExportCsv|ObjectProphecy */
    private $exportCsv;
    /** @var SendEmailToCustomer|ObjectProphecy */
    private $sendEmailToCustomer;

    public function setUp(): void
    {
        parent::setUp();

        $this->metadata = $this->prophesize(MetadataInterface::class);
        $this->requestConfigurationFactory = $this->prophesize(RequestConfigurationFactoryInterface::class);
        $this->viewHandler = $this->prophesize(ViewHandlerInterface::class);
        $this->repository = $this->prophesize(RepositoryInterface::class);
        $this->factory = $this->prophesize(FactoryInterface::class);
        $this->newResourceFactory = $this->prophesize(NewResourceFactoryInterface::class);
        $this->manager = $this->prophesize(ObjectManager::class);
        $this->singleResourceProvider = $this->prophesize(SingleResourceProviderInterface::class);
        $this->resourcesFinder = $this->prophesize(ResourcesCollectionProviderInterface::class);
        $this->resourceFormFactory = $this->prophesize(ResourceFormFactoryInterface::class);
        $this->redirectHandler = $this->prophesize(RedirectHandlerInterface::class);
        $this->flashHelper = $this->prophesize(FlashHelperInterface::class);
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->stateMachine = $this->prophesize(StateMachineInterface::class);
        $this->resourceUpdateHandler = $this->prophesize(ResourceUpdateHandlerInterface::class);
        $this->resourceDeleteHandler = $this->prophesize(ResourceDeleteHandlerInterface::class);
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->doctrine = $this->prophesize(ManagerRegistry::class);
        $this->objectManager = $this->prophesize(ObjectManager::class);
        $this->resource = $this->prophesize(OrderInterface::class);
        $this->customer = $this->prophesize(Customer::class);
        $this->zendeskClient = $this->prophesize(HttpClient::class);
        $this->tickets = $this->prophesize(Tickets::class);
        $this->order = $this->prophesize(Order::class);
        $this->formBuilder = $this->prophesize(FormBuilderInterface::class);
        $this->orderService = $this->prophesize(OrderService::class);
        $this->exportCsv = $this->prophesize(ExportCsv::class);
        $this->sendEmailToCustomer = $this->prophesize(SendEmailToCustomer::class);

        $this->singleResourceProvider->get(Argument::any(), Argument::any())->willReturn($this->resource->reveal());
        $this->resource->hasShipments()->willReturn(false);
        $this->resource->getCustomer()->willReturn($this->customer->reveal());
        $this->customer->getId()->willReturn(1);
        $this->doctrine->getManager(Argument::any())->willReturn($this->objectManager->reveal());
        $this->doctrine->getManagerForClass(Argument::any())->willReturn($this->objectManager->reveal());
        $this->container->has(Argument::exact('doctrine'))->willReturn(true);
        $this->container->get(Argument::exact('doctrine'))->willReturn($this->doctrine->reveal());
        $this->objectManager->getRepository(Argument::any())->willReturn($this->repository->reveal());
        $this->container->get(Argument::exact('client.zendesk'))->willReturn($this->zendeskClient->reveal());
        $this->zendeskClient->tickets()->willReturn($this->tickets->reveal());
        $this->container->get(Argument::exact('form.factory'))->willReturn($this->formBuilder);
    }

    public function testShowWithTicketsAction(): void
    {
        $controller = new OrderController(
            $this->metadata->reveal(),
            $this->requestConfigurationFactory->reveal(),
            $this->viewHandler->reveal(),
            $this->repository->reveal(),
            $this->factory->reveal(),
            $this->newResourceFactory->reveal(),
            $this->manager->reveal(),
            $this->singleResourceProvider->reveal(),
            $this->resourcesFinder->reveal(),
            $this->resourceFormFactory->reveal(),
            $this->redirectHandler->reveal(),
            $this->flashHelper->reveal(),
            $this->authorizationChecker->reveal(),
            $this->eventDispatcher->reveal(),
            $this->stateMachine->reveal(),
            $this->resourceUpdateHandler->reveal(),
            $this->resourceDeleteHandler->reveal()
        );
        $controller->setContainer($this->container->reveal());

        $this->repository->findBy(Argument::any())->willReturn([]);

        $request = new Request();
        $response = new Response();

        $this->markTestSkipped(true);

        $requestConfiguration = $this->prophesize(RequestConfiguration::class);
        $this->requestConfigurationFactory->create($this->metadata, $request)->willReturn($requestConfiguration->reveal());
        $resourceControllerEvent = $this->prophesize(ResourceControllerEvent::class);
        $this->eventDispatcher->dispatch(ResourceActions::SHOW, $requestConfiguration->reveal(), $this->resource->reveal())->willReturn($resourceControllerEvent->reveal());
        $this->viewHandler->handle($requestConfiguration->reveal(), View::create($this->resource))->willReturn($response);

        $controller->showAction($request);

        $this->container->get('client.zendesk')->shouldBeCalledTimes(1);
        $this->tickets->findMany()->shouldNotBeCalled();
    }

    public function testShowWithoutTicketsAction(): void
    {
        $controller = new OrderController(
            $this->metadata->reveal(),
            $this->requestConfigurationFactory->reveal(),
            $this->viewHandler->reveal(),
            $this->repository->reveal(),
            $this->factory->reveal(),
            $this->newResourceFactory->reveal(),
            $this->manager->reveal(),
            $this->singleResourceProvider->reveal(),
            $this->resourcesFinder->reveal(),
            $this->resourceFormFactory->reveal(),
            $this->redirectHandler->reveal(),
            $this->flashHelper->reveal(),
            $this->authorizationChecker->reveal(),
            $this->eventDispatcher->reveal(),
            $this->stateMachine->reveal(),
            $this->resourceUpdateHandler->reveal(),
            $this->resourceDeleteHandler->reveal()
        );
        $controller->setContainer($this->container->reveal());

        $dt1 = new \DateTime();
        $dt2 = new \DateTime('-1 hour');
        $support1 = new CustomerSupport();
        $support1->setCreatedAt($dt1);
        $support1->setId(1);
        $support1->setSupportId(123);
        $support1->setCustomer($this->customer->reveal());

        $support2 = new CustomerSupport();
        $support2->setCreatedAt($dt2);
        $support2->setId(2);
        $support2->setSupportId(456);
        $support2->setCustomer($this->customer->reveal());

        $tickets = [
            [
                'id' => 123,
                'subject' => 'Subject 1',
                'created_at' => $dt1,
            ], [
                'id' => 456,
                'subject' => 'Subject 2',
                'created_at' => $dt2,
            ],
        ];

        $customerSupports = [$support1, $support2];
        $this->repository->findBy(Argument::any())->willReturn($customerSupports);
        $this->tickets->findMany(Argument::exact([123, 456]))->willReturn($tickets);

        $request = new Request();
        $response = new Response();

        $this->markTestSkipped(true);

        $requestConfiguration = $this->prophesize(RequestConfiguration::class);
        $this->requestConfigurationFactory->create($this->metadata, $request)->willReturn($requestConfiguration->reveal());
        $resourceControllerEvent = $this->prophesize(ResourceControllerEvent::class);
        $this->eventDispatcher->dispatch(ResourceActions::SHOW, $requestConfiguration->reveal(), $this->resource->reveal())->willReturn($resourceControllerEvent->reveal());
        $this->viewHandler->handle($requestConfiguration->reveal(), View::create($this->resource))->willReturn($response);

        $controller->showAction($request);

        $this->container->get('client.zendesk')->shouldBeCalledTimes(1);
        $this->tickets->findMany([123, 456])->shouldBeCalledTimes(1);
    }
}
