<?php

namespace App\Tests\Unit\ReorderProcessing;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\ReorderProcessing\ReorderItemsProcessor;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ReorderItemsProcessorTest extends TestCase
{
    /** @var Order */
    private $order;

    /** @var Order */
    private $reorder;

    private OrderItem $item;
    private OrderItem $otherItem;

    /**
     * @var FactoryInterface|(FactoryInterface&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderItemFactory;

    /**
     * @var OrderItemQuantityModifierInterface|(OrderItemQuantityModifierInterface&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderItemQuantityModifier;

    /** @var ReorderItemsProcessor */
    private $processor;

    private readonly EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->reorder = new Order();
        $this->item = new OrderItem();
        $this->otherItem = new OrderItem();
        $this->orderItemFactory = $this->createMock(FactoryInterface::class);
        $this->orderItemFactory
            ->method('createNew')
            ->willReturn($this->item);
        $this->orderItemFactory
            ->method('createNew')
            ->willReturn($this->otherItem);
        $this->orderItemQuantityModifier = $this->createMock(OrderItemQuantityModifierInterface::class);

        $this->entityManager = $this->createMock(EntityManager::class);
    }

    public function setUpProcessor()
    {
        $this->processor = new ReorderItemsProcessor(
            $this->orderItemFactory,
            $this->orderItemQuantityModifier,
            $this->entityManager
        );
    }

    private function setUpNoErrors()
    {
        $variant = new ProductVariant();
        $this->item->setVariant($variant);

        $otherVariant = new ProductVariant();
        $this->otherItem->setVariant($otherVariant);

        $this->order->addItem($this->item);
        $this->order->addItem($this->otherItem);

        $this->setUpProcessor();
    }

    public function testProcess(): void
    {
        $this->setUpNoErrors();
        $this->processor->process($this->order, $this->reorder);

        $this->assertEquals($this->order->getItems()->count(), $this->reorder->getItems()->count());

        foreach ($this->order->getItems() as $item) {
            $this->assertNotEmpty($this->reorder->getItems()->filter(fn ($currentItem): bool => $currentItem->getVariant() === $item->getVariant()));
        }
    }

    public function testProcessEmptyItems(): void
    {
        $this->setUpNoErrors();
        $this->order->getItems()->clear();

        $this->processor->process($this->order, $this->reorder);

        $this->assertTrue(true);
    }

    public function testProcessEmptyReorderItems(): void
    {
        $this->setUpNoErrors();
        $this->reorder->getItems()->clear();

        $this->processor->process($this->order, $this->reorder);

        $this->assertEquals($this->order->getItems()->count(), $this->reorder->getItems()->count());

        foreach ($this->order->getItems() as $item) {
            $this->assertNotEmpty($this->reorder->getItems()->filter(fn ($currentItem): bool => $currentItem->getVariant() === $item->getVariant()));
        }
    }
}
