<?php

namespace App\Tests\Unit\Component\Order\Processor;

use App\Component\Order\Processor\CompositeReorderProcessor;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Order;
use App\Services\Channel\ChannelCode;
use App\Tests\TestCase;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\Adjustment;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Channel;

class CompositeReorderProcessorTest extends TestCase
{
    private ReorderProcessor $reorderProcessor;

    private CompositeReorderProcessor $composite;

    public function setUp(): void
    {
        parent::setUp();

        $this->reorderProcessor = $this->createMock(ReorderProcessor::class);
        $this->composite = new CompositeReorderProcessor();
    }

    public function testAddProcessor(): void
    {
        $this->composite->addProcessor($this->reorderProcessor, 0, ChannelCode::CHANNEL_BIS);

        $this->assertArrayHasKey(ChannelCode::CHANNEL_BIS, $this->composite->getPostProcessors());
        $this->assertTrue($this->composite->getPostProcessors()[ChannelCode::CHANNEL_BIS]->contains($this->reorderProcessor));
    }

    public function testAddProcessorPre(): void
    {
        $this->composite->addProcessor($this->reorderProcessor, 0, ChannelCode::CHANNEL_BIS, true);

        $this->assertArrayHasKey(ChannelCode::CHANNEL_BIS, $this->composite->getPreProcessors());
        $this->assertTrue($this->composite->getPreProcessors()[ChannelCode::CHANNEL_BIS]->contains($this->reorderProcessor));
    }

    public function testAddProcessorPreMultiple(): void
    {
        $this->composite->addProcessor($this->reorderProcessor, 0, ChannelCode::CHANNEL_BIS . '_' . ChannelCode::CHANNEL_RETURN, true);

        $this->assertArrayHasKey(ChannelCode::CHANNEL_BIS, $this->composite->getPreProcessors());
        $this->assertArrayHasKey(ChannelCode::CHANNEL_RETURN, $this->composite->getPreProcessors());
        $this->assertTrue($this->composite->getPreProcessors()[ChannelCode::CHANNEL_BIS]->contains($this->reorderProcessor));
        $this->assertTrue($this->composite->getPreProcessors()[ChannelCode::CHANNEL_RETURN]->contains($this->reorderProcessor));
    }

    public function testAddProcessorPostMultiple(): void
    {
        $this->composite->addProcessor($this->reorderProcessor, 0, ChannelCode::CHANNEL_BIS . '_' . ChannelCode::CHANNEL_RETURN);

        $this->assertArrayHasKey(ChannelCode::CHANNEL_BIS, $this->composite->getPostProcessors());
        $this->assertArrayHasKey(ChannelCode::CHANNEL_RETURN, $this->composite->getPostProcessors());
        $this->assertTrue($this->composite->getPostProcessors()[ChannelCode::CHANNEL_BIS]->contains($this->reorderProcessor));
        $this->assertTrue($this->composite->getPostProcessors()[ChannelCode::CHANNEL_RETURN]->contains($this->reorderProcessor));
    }

    public function testAddProcessorPostAlreadyExists(): void
    {
        $newReorder = $this->createMock(ReorderProcessor::class);
        $this->composite->addProcessor($this->reorderProcessor, 0, ChannelCode::CHANNEL_BIS);
        $this->composite->addProcessor($newReorder, 0, ChannelCode::CHANNEL_BIS);

        $this->assertArrayHasKey(ChannelCode::CHANNEL_BIS, $this->composite->getPostProcessors());
        $this->assertTrue($this->composite->getPostProcessors()[ChannelCode::CHANNEL_BIS]->contains($this->reorderProcessor));
        $this->assertTrue($this->composite->getPostProcessors()[ChannelCode::CHANNEL_BIS]->contains($newReorder));
        $this->assertEquals(2, $this->composite->getPostProcessors()[ChannelCode::CHANNEL_BIS]->count());
    }

    public function testAddProcessorPreAlreadyExists(): void
    {
        $newReorder = $this->createMock(ReorderProcessor::class);
        $this->composite->addProcessor($this->reorderProcessor, 0, ChannelCode::CHANNEL_BIS, true);
        $this->composite->addProcessor($newReorder, 0, ChannelCode::CHANNEL_BIS, true);

        $this->assertArrayHasKey(ChannelCode::CHANNEL_BIS, $this->composite->getPreProcessors());
        $this->assertTrue($this->composite->getPreProcessors()[ChannelCode::CHANNEL_BIS]->contains($this->reorderProcessor));
        $this->assertTrue($this->composite->getPreProcessors()[ChannelCode::CHANNEL_BIS]->contains($newReorder));
        $this->assertEquals(2, $this->composite->getPreProcessors()[ChannelCode::CHANNEL_BIS]->count());
    }

    public function testConstruct(): void
    {
        $this->assertIsArray($this->composite->getPreProcessors());
        $this->assertIsArray($this->composite->getPostProcessors());
        $this->assertEmpty($this->composite->getPreProcessors());
        $this->assertIsArray($this->composite->getPostProcessors());
    }

    public function testItRemovesTheShippingAdjustmentWhenProcessingAReturn(): void
    {
        self::bootKernel();

        $order = new Order();
        $order->setLocaleCode('fr_FR');
        $order->setShippingAddress(new Address());
        $order->setBillingAddress(new Address());

        $reorder = new Order();
        $reorder->setChannel(new Channel());

        $shippingAdjustment = new Adjustment();
        $shippingAdjustment->setType(AdjustmentInterface::SHIPPING_ADJUSTMENT);
        $shippingAdjustment->setAmount(1000);

        $reorder->addAdjustment($shippingAdjustment);

        /** @var CompositeReorderProcessor $compositeProcessor */
        $compositeProcessor = self::$container->get(CompositeReorderProcessor::class);

        $compositeProcessor->process($order, $reorder, 'return', true);

        self::assertEmpty($reorder->getAdjustmentsRecursively(AdjustmentInterface::SHIPPING_ADJUSTMENT));
        self::assertEquals(0, $reorder->getAdjustmentsTotalRecursively(AdjustmentInterface::SHIPPING_ADJUSTMENT));
    }
}
