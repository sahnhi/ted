<?php

namespace App\Tests\Unit\Factory;

use App\Doctrine\Entity\Sylius\Order;
use App\Factory\OrderFactory;
use App\Factory\OrderFactoryInterface;
use App\Factory\ShipmentFactoryInterface;
use App\Services\Channel\ChannelCode;
use App\Services\OrderReturn\ReturnTypeService;
use App\Tests\TestCase;
use Sylius\AdminOrderCreationPlugin\Factory\OrderFactoryInterface as BaseOrderFactoryInterface;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Channel\Model\Channel;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\TokenAssigner\OrderTokenAssignerInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\InvalidArgumentException;

class OrderFactoryTest extends TestCase
{
    public const TEST_CUSTOMER_ID = '0001';
    public const TEST_ORDER_NUMBER = '000001';

    private Order $order;

    private OrderFactoryInterface $factory;

    private BaseOrderFactoryInterface $decoratedFactory;

    private ChannelRepositoryInterface $channelRepository;

    private ReorderProcessor $appReorderProcessor;

    private ReorderProcessor $pluginReorderProcessor;

    private readonly ShipmentFactoryInterface $shipmentFactory;

    private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier;

    private readonly FactoryInterface $orderItemFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
        $this->decoratedFactory = $this->createMock(OrderFactoryInterface::class);
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->appReorderProcessor = $this->createMock(ReorderProcessor::class);
        $this->pluginReorderProcessor = $this->createMock(ReorderProcessor::class);
        $this->tokenAssigner = $this->createMock(OrderTokenAssignerInterface::class);
        $this->shipmentFactory = $this->createMock(ShipmentFactoryInterface::class);
        $this->orderItemQuantityModifier = $this->createMock(OrderItemQuantityModifierInterface::class);
        $this->orderItemFactory = $this->createMock(FactoryInterface::class);

        $this->order->setNumber(self::TEST_ORDER_NUMBER);
    }

    private function setUpProcessor(): void
    {
        $this->factory = new OrderFactory(
            $this->decoratedFactory,
            $this->channelRepository,
            $this->appReorderProcessor,
            $this->pluginReorderProcessor,
            $this->tokenAssigner,
            $this->shipmentFactory,
            $this->orderItemQuantityModifier,
            $this->orderItemFactory
        );
    }

    private function setUpNoErrors(string $channelCode = ChannelCode::CHANNEL_RETURN)
    {
        $this->decoratedFactory
            ->method('createNew')
            ->willReturn(new Order());

        $this->decoratedFactory
            ->method('createForCustomerAndChannel')
            ->willReturn(new Order());

        $this->decoratedFactory
            ->method('createFromExistingOrder')
            ->willReturn(new Order());

        $this->channelRepository
            ->method('findOneByCode')
            ->willReturnCallback(function () use ($channelCode) {
                $channel = new Channel();
                $channel->setCode($channelCode);

                return $channel;
            });

        $this->setUpProcessor();
    }

    public function testCreateReorder(): void
    {
        $this->setUpNoErrors(ChannelCode::CHANNEL_BIS);
        $reorder = $this->factory->createReorder($this->order, ChannelCode::CHANNEL_BIS);

        $this->assertNotNull($reorder->getParentNumber());
        $this->assertEquals($this->order->getNumber(), $reorder->getParentNumber());
        $this->assertNotNull($reorder->getChannel());
        $this->assertEquals(ChannelCode::CHANNEL_BIS, $reorder->getChannel()->getCode());
    }

    public function testCreateReorderWithType(): void
    {
        $this->setUpNoErrors();
        $reorder = $this->factory->createReorder($this->order, ChannelCode::CHANNEL_RETURN, ReturnTypeService::RETURN_TYPE_EXCHANGE);

        $this->assertNotNull($reorder->getParentNumber());
        $this->assertEquals($this->order->getNumber(), $reorder->getParentNumber());
        $this->assertNotNull($reorder->getChannel());
        $this->assertEquals(ChannelCode::CHANNEL_RETURN, $reorder->getChannel()->getCode());
    }

    public function testCreateReorderChannelNullError(): void
    {
        $this->channelRepository
            ->method('findOneByCode')
            ->willReturn(null);

        $this->setUpNoErrors();
        $this->expectException(InvalidArgumentException::class);

        $this->factory->createReorder($this->order, ChannelCode::CHANNEL_BIS);
    }

    public function testCreateFromExistingOrder(): void
    {
        $this->setUpNoErrors();
        $order = $this->factory->createFromExistingOrder($this->order);

        $this->assertInstanceOf(OrderInterface::class, $order);
    }

    public function testCreateNew(): void
    {
        $this->setUpNoErrors();
        $order = $this->factory->createNew();

        $this->assertInstanceOf(OrderInterface::class, $order);
    }

    public function testCreateForCustomerAndChannel(): void
    {
        $this->setUpNoErrors();
        $order = $this->factory->createForCustomerAndChannel(self::TEST_CUSTOMER_ID, ChannelCode::CHANNEL_BIS);

        $this->assertInstanceOf(OrderInterface::class, $order);
    }
}
