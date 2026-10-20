<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Doctrine\Entity\OrderReason;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\Sylius\ProductVariantRepository;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\ReorderProcessing\BisExchangeFormProcessor;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\AdminUser;
use Sylius\Component\Order\Factory\OrderItemUnitFactory;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifier;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\InvalidArgumentException;

class BisExchangeFormProcessorTest extends TestCase
{
    private Order $order;
    private AdminUser $admin;
    private ProductVariant $productVariant;
    private Reason $offer;
    private Order $reorder;
    private Reason $reason;
    private array $formData = [];
    /** @var Request */
    private $requestStack;
    /** @var MockObject|EntityRepository */
    private $reasonRepository;
    /** @var MockObject|ProductVariantRepository */
    private $productVariantRepository;
    /** @var MockObject|OrderItemQuantityModifier */
    private $orderItemQuantityModifier;
    /** @var MockObject|EntityRepository */
    private $adminUserRepository;
    /** @var MockObject|FactoryInterface */
    private $orderItemFactory;
    /** @var MockObject|EntityManagerInterface */
    private $manager;
    /** @var MockObject|OrderProcessorInterface */
    private $orderProcessor;
    private BisExchangeFormProcessor $processor;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
        $this->reason = new Reason();
        $this->offer = new Reason();
        $this->admin = new AdminUser();
        $this->productVariant = new ProductVariant();
        $this->requestStack = new RequestStack();
        $this->reasonRepository = $this->createMock(RepositoryInterface::class);
        $this->productVariantRepository = $this->createMock(ProductVariantRepositoryInterface::class);
        $this->orderItemQuantityModifier = new OrderItemQuantityModifier(new OrderItemUnitFactory(OrderItemUnit::class));
        $this->adminUserRepository = $this->createMock(RepositoryInterface::class);
        $this->orderItemFactory = $this->createMock(FactoryInterface::class);
        $this->manager = $this->createMock(EntityManagerInterface::class);
        $this->orderProcessor = $this->createMock(OrderProcessorInterface::class);

        $orderItem = new OrderItem();
        new OrderItemUnit($orderItem);
        $this->reorder->addItem($orderItem);

        $this->productVariant->setCurrentLocale(self::LOCALE_CODE);
        $this->productVariant->setProduct(new Product());
        $this->productVariant->setName('productVariantName');
        $this->productVariant->getProduct()->setCurrentLocale(self::LOCALE_CODE);
        $this->productVariant->getProduct()->setName('productName');
    }

    public function setUpProcessor(): void
    {
        $this->processor = new BisExchangeFormProcessor(
            $this->requestStack,
            $this->reasonRepository,
            $this->productVariantRepository,
            $this->orderItemQuantityModifier,
            $this->adminUserRepository,
            $this->createMock(FactoryInterface::class),
        );
    }

    private function setUpNoErrors()
    {
        $request = new Request();
        $request->query->set('sylius_admin_order_creation_new_order', $this->formData);

        $this->requestStack->push($request);

        $this->setUpProcessor();
    }

    private function setUpHandleReasonNoErrors(array $bisExchange = [])
    {
        $this->formData = [
            'bisExchange' => $bisExchange,
        ];

        $this->adminUserRepository
            ->method('findOneBy')
            ->willReturn($this->admin);

        $this->productVariantRepository
            ->method('findOneBy')
            ->willReturn($this->productVariant);

        $this->reasonRepository
            ->method('findOneBy')
            ->willReturn($this->reason, $this->offer);

        $this->setUpNoErrors();
    }

    private function setUpHandleProductVariantNoErrors(array $bisItems = [])
    {
        $this->formData = [
            'bisItems' => $bisItems,
        ];

        $this->productVariantRepository
            ->method('findOneBy')
            ->willReturn($this->productVariant);

        $this->setUpNoErrors();
    }

    private function setUpFormDataBisExchange(): array
    {
        $bisExchange = [];
        $bisExchange[] = [
            'reason' => '',
            'variant' => '',
            'commercialOffer' => '',
            'createdById' => '',
            'quantity' => '1',
            'comment' => '',
        ];

        return $bisExchange;
    }

    private function setUpFormDataBisItems(): array
    {
        $bisItems = [];
        $bisItems[] = [
            'variant' => '',
            'quantity' => '1',
        ];

        return $bisItems;
    }

    public function testProcess(): void
    {
        $this->setUpNoErrors();
        $this->processor->process($this->order, $this->reorder);
        $this->assertEquals(true, true);
    }

    public function testProcessNoFormData(): void
    {
        $request = new Request();
        $this->requestStack->push($request);

        $this->setUpProcessor();
        $this->expectException(InvalidArgumentException::class);

        $this->processor->process($this->order, $this->reorder);
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataBisExchangeEmpty(): void
    {
        $this->setUpHandleReasonNoErrors();
        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        $this->assertEquals($this->reorder, $order);
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataEmpty(): void
    {
        $request = new Request();
        $this->requestStack->push($request);

        $this->setUpProcessor();
        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        $this->assertEquals($this->reorder, $order);
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormData(): void
    {
        $bisExchange = $this->setUpFormDataBisExchange();
        $this->setUpHandleReasonNoErrors($bisExchange);

        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        $this->assertNotEmpty($order->getOrderReason());
        $this->assertEquals(count($bisExchange), $order->getOrderReason()->count());

        foreach ($order->getOrderReason() as $key => $orderReason) {
            $this->assertInstanceOf(OrderReason::class, $orderReason);
            $this->assertEquals($this->reason, $orderReason->getReason());
            $this->assertEquals($this->reorder, $orderReason->getOrder());
            $this->assertFalse($orderReason->getIsProblem());
            $this->assertEquals($this->admin, $orderReason->getCreatedBy());
            $this->assertEquals($this->productVariant, $orderReason->getVariant());
            $this->assertEquals((int) $bisExchange[$key]['quantity'], $orderReason->getQuantity());
            $this->assertEquals($this->offer, $orderReason->getCommercialOffer());
            $this->assertNotNull($orderReason->getComment());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataNoUser(): void
    {
        $this->adminUserRepository = $this->createMock(EntityRepository::class);
        $this->adminUserRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->setUpHandleReasonNoErrors($this->setUpFormDataBisExchange());

        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        foreach ($order->getOrderReason() as $orderReason) {
            $this->assertNull($orderReason->getCreatedBy());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataNoReason(): void
    {
        $this->reasonRepository = $this->createMock(EntityRepository::class);
        $this->reasonRepository
            ->method('findOneBy')
            ->willReturn(null, $this->offer);

        $this->setUpHandleReasonNoErrors($this->setUpFormDataBisExchange());

        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        foreach ($order->getOrderReason() as $orderReason) {
            $this->assertNull($orderReason->getReason());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataNoOffer(): void
    {
        $this->reasonRepository = $this->createMock(EntityRepository::class);
        $this->reasonRepository
            ->method('findOneBy')
            ->willReturn($this->reason, null);

        $this->setUpHandleReasonNoErrors($this->setUpFormDataBisExchange());

        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        foreach ($order->getOrderReason() as $orderReason) {
            $this->assertNull($orderReason->getCommercialOffer());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataNoProductVariant(): void
    {
        $this->productVariantRepository = $this->createMock(EntityRepository::class);
        $this->productVariantRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->setUpHandleReasonNoErrors($this->setUpFormDataBisExchange());

        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        foreach ($order->getOrderReason() as $orderReason) {
            $this->assertNull($orderReason->getVariant());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataNoReasonIndex(): void
    {
        $this->setUpHandleReasonNoErrors($this->setUpFormDataBisExchange());
        unset($this->formData['bisExchange'][0]['reason']);

        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        foreach ($order->getOrderReason() as $orderReason) {
            $this->assertNull($orderReason->getReason());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataNoVariantIndex(): void
    {
        $this->setUpHandleReasonNoErrors($this->setUpFormDataBisExchange());
        unset($this->formData['bisExchange'][0]['variant']);

        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        foreach ($order->getOrderReason() as $orderReason) {
            $this->assertNull($orderReason->getVariant());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataNoOfferIndex(): void
    {
        $this->setUpHandleReasonNoErrors($this->setUpFormDataBisExchange());
        unset($this->formData['bisExchange'][0]['commercialOffer']);

        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        foreach ($order->getOrderReason() as $orderReason) {
            $this->assertNull($orderReason->getCommercialOffer());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataNoCreateByIdIndex(): void
    {
        $this->setUpHandleReasonNoErrors($this->setUpFormDataBisExchange());
        unset($this->formData['bisExchange'][0]['createdById']);

        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        foreach ($order->getOrderReason() as $orderReason) {
            $this->assertNull($orderReason->getCreatedBy());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataNoQuantityIndexError(): void
    {
        $this->setUpHandleReasonNoErrors($this->setUpFormDataBisExchange());
        unset($this->formData['bisExchange'][0]['quantity']);

        $this->expectException(InvalidArgumentException::class);

        $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleReasonFormDataNoCommentIndex(): void
    {
        $this->setUpHandleReasonNoErrors($this->setUpFormDataBisExchange());
        unset($this->formData['bisExchange'][0]['comment']);

        $order = $this->invokeMethod($this->processor, 'handleReasonFormData', [$this->formData, $this->reorder]);

        foreach ($order->getOrderReason() as $orderReason) {
            $this->assertNull($orderReason->getComment());
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleProductVariantFormDataNoVariantIndexError(): void
    {
        $this->setUpHandleProductVariantNoErrors($this->setUpFormDataBisItems());
        unset($this->formData['bisItems'][0]['variant']);

        $this->expectException(InvalidArgumentException::class);

        $this->invokeMethod($this->processor, 'handleProductVariantFormData', [$this->formData, $this->reorder]);
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleProductVariantFormDataNoQuantityIndexError(): void
    {
        $this->setUpHandleProductVariantNoErrors($this->setUpFormDataBisItems());
        unset($this->formData['bisItems'][0]['quantity']);

        $this->expectException(InvalidArgumentException::class);

        $this->invokeMethod($this->processor, 'handleProductVariantFormData', [$this->formData, $this->reorder]);
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleProductVariantNullError(): void
    {
        $this->productVariantRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->setUpHandleProductVariantNoErrors($this->setUpFormDataBisItems());

        $this->expectException(InvalidArgumentException::class);

        $this->invokeMethod($this->processor, 'handleProductVariantFormData', [$this->formData, $this->reorder]);
    }

    /**
     * @throws \ReflectionException
     */
    public function testHandleProductVariantProductNullError(): void
    {
        $this->setUpHandleProductVariantNoErrors($this->setUpFormDataBisItems());
        $this->productVariant->setProduct(null);

        $this->expectException(InvalidArgumentException::class);

        $this->invokeMethod($this->processor, 'handleProductVariantFormData', [$this->formData, $this->reorder]);
    }
}
