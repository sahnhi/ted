<?php

declare(strict_types=1);

namespace App\Tests\Functional\Processor\OrderMessage;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Processor\OrderMessage\OrderMessageProcessor;
use App\Processor\OrderMessage\Processors\BedFrameTooLargeProcessor;
use App\Processor\OrderMessage\Processors\BedHeadAndWoodBedFrameProcessor;
use App\Processor\OrderMessage\Processors\BundleAvailableProcessor;
use App\Processor\OrderMessage\Processors\BundleBedFrameTooLargeProcessor;
use App\Processor\OrderMessage\Processors\BundleComboBedMonoblocProcessor;
use App\Processor\OrderMessage\Processors\BundleComboBedStorageProcessor;
use App\Processor\OrderMessage\Processors\PackOnCartProcessor;
use App\Processor\OrderMessage\Processors\StorageBedFrameTooLargeProcessor;
use App\Processor\OrderMessage\Processors\VoluminousProductOnCartProcessor;
use App\Processor\OrderMessage\Processors\WoodBedHeadAndOtherBedFrameProcessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This test suite verifies the functionality of the OrderMessageProcessor, which handles various order-related messages.
 * The processor is responsible for adding appropriate messages to orders based on specific product combinations and conditions.
 *
 * Test cases include:
 * - Storage bed frame size validation (testStorageBedFrameTooLarge)
 * - Bundle availability messaging when specific product combinations are present (testBundleAvailable)
 * - Multiple message handling for bundle + storage bed frame combinations (testBundleAvailableAndStorageBedFrameTooLarge)
 * - Pack product messaging (testPackOnCart)
 * - Bed head and wood bed frame compatibility checks (testBedHeadAndWoodBedFrame)
 * - Complex bed component combinations with storage frames (testBedHeadAndWoodBedFrameAndStorageBedFrame)
 * - Wood bed head compatibility with other bed frames (testWoodBedHeadAndOtherBedFrame)
 * - Bed frame size validation (testBedFrameTooLarge)
 * - Bundle bed frame size validation (testBundleBedFrameTooLarge)
 * - Bundle combo bed monobloc validation (testBundleComboBedMonobloc)
 * - Combined bundle combo and storage bed frame validation (testBundleComboBedMonoblocAndStorageBedFrame)
 *
 * Each test verifies:
 * - Correct message content is added
 * - Proper message type is set
 * - Messages are added/removed appropriately as cart contents change
 * - Multiple messages are handled correctly when needed
 */
final class OrderMessageProcessorTest extends KernelTestCase
{
    private readonly OrderMessageProcessor $processor;

    private array $data;

    public function setUp(): void
    {
        self::bootKernel();
        $this->processor = self::$container->get(OrderMessageProcessor::class);
        $this->data = static::$kernel->getContainer()->getParameter('order_message');
    }

    /**
     * In case of storage bed frame => creates a message advise the about the size of the bed frame.
     */
    public function testStorageBedFrameTooLarge(): void
    {
        try {
            $this->checkMessageIsActivated(StorageBedFrameTooLargeProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn('storage-bed-frame');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order = new Order();
        $order->addItem($orderItem);

        $data = $this->data[StorageBedFrameTooLargeProcessor::ENTRY_NAME];

        $this->processor->process($order);

        self::assertEquals(count($order->getMessages()), 1);
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }

    private function checkMessageIsActivated(string $messageKey): void
    {
        if (!isset($this->data[$messageKey])) {
            throw new \UnexpectedValueException(sprintf('Message key "%s" not found in data', $messageKey));
        }
    }

    /**
     * In case a use has both a mattress and a bed frame => creates a message advise the about the bundles
     * available for purchase.
     */
    public function testBundleAvailable(): void
    {
        try {
            $this->checkMessageIsActivated(BundleAvailableProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $data = $this->data[BundleAvailableProcessor::ENTRY_NAME];

        $order = new Order();

        $mockProducts = [
            'mattress' => 'go-mattress',
            'steel-bed-frame' => 'steel-bed-frame',
        ];

        // only mattress on cart must not have message
        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn($mockProducts['mattress']);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(count($order->getMessages()), 0);

        // adding bedframe to same cart must trigger a message
        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn($mockProducts['steel-bed-frame']);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(count($order->getMessages()), 1);
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }

    public function testBundleAvailableAndStorageBedFrameTooLarge(): void
    {
        try {
            $this->checkMessageIsActivated(BundleAvailableProcessor::ENTRY_NAME);
            $this->checkMessageIsActivated(StorageBedFrameTooLargeProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $order = new Order();

        $mockProducts = [
            'mattress',
            'storage-bed-frame',
        ];

        foreach ($mockProducts as $mockProduct) {
            $product = $this->createMock(Product::class);
            $product->method('getCode')->willReturn($mockProduct);

            $productVariant = $this->createMock(ProductVariant::class);
            $productVariant->method('getCode')->willReturn('TED-DUMMY');

            $orderItem = $this->createMock(OrderItem::class);
            $orderItem->method('getProduct')->willReturn($product);
            $orderItem->method('getVariant')->willReturn($productVariant);

            $order->addItem($orderItem);
        }

        $data = $this->data[BundleAvailableProcessor::ENTRY_NAME];
        $dataStorage = $this->data[StorageBedFrameTooLargeProcessor::ENTRY_NAME];

        $this->processor->process($order);

        self::assertEquals(count($order->getMessages()), 2);
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
        self::assertEquals($dataStorage['messages']['message'], $order->getMessages()[1]->getMessage());
        self::assertEquals($dataStorage['messages']['type'], $order->getMessages()[1]->getType());
    }

    public function testPackOnCart(): void
    {
        try {
            $this->checkMessageIsActivated(PackOnCartProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn('mattress-pack');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order = new Order();
        $order->addItem($orderItem);

        $data = $this->data[PackOnCartProcessor::ENTRY_NAME];

        $this->processor->process($order);

        self::assertEquals(count($order->getMessages()), 1);
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }

    /**
     * In case an user has an wood bedframe => checks he has the compatible bed head.
     */
    public function testBedHeadAndWoodBedFrame(): void
    {
        try {
            $this->checkMessageIsActivated(PackOnCartProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $order = new Order();

        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn('mattress-pack');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $data = $this->data[PackOnCartProcessor::ENTRY_NAME];

        $this->processor->process($order);

        self::assertEquals(count($order->getMessages()), 1);
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }

    /**
     * In case an user has an wood bedframe and a bed head => checks he has the compatible bed head.
     */
    public function testBedHeadAndWoodBedFrameAndStorageBedFrame(): void
    {
        try {
            $this->checkMessageIsActivated(BedHeadAndWoodBedFrameProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $data = $this->data[BedHeadAndWoodBedFrameProcessor::ENTRY_NAME];

        $order = new Order();

        $mockProducts = [
            'wood-bed-frame' => 'wood-bed-frame',
            'bed-head' => 'bed-head',
            'wood-bed-head' => 'wood-bed-head',
        ];

        // only the wood bed frame on cart must not have message
        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn($mockProducts['wood-bed-frame']);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(count($order->getMessages()), 0);

        // adding regular bed head to same cart must trigger a message
        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn($mockProducts['bed-head']);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(count($order->getMessages()), 1);
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());

        // adding wood bed head to same cart must not trigger a message
        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn($mockProducts['wood-bed-head']);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(count($order->getMessages()), 0);
    }

    /**
     * In case an user has an wood bedhead => checks he has the compatible bed frame.
     */
    public function testWoodBedHeadAndOtherBedFrame(): void
    {
        $data = $this->data[WoodBedHeadAndOtherBedFrameProcessor::ENTRY_NAME];

        $order = new Order();

        $mockProducts = [
            'wood-bed-head' => 'wood-bed-head',
            'steel-bed-frame' => 'steel-bed-frame',
            'wood-bed-frame' => 'wood-bed-frame',
        ];

        // only the wood bed head on cart must have message
        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn($mockProducts['wood-bed-head']);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(1, count($order->getMessages()));
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());

        // adding regular bed frame to same cart must trigger a message
        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn($mockProducts['steel-bed-frame']);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(1, count($order->getMessages()));
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());

        // adding wood bed frame to same cart must not trigger a message
        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn($mockProducts['wood-bed-frame']);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TED-DUMMY');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(0, count($order->getMessages()));
    }

    public function testBedFrameTooLarge(): void
    {
        try {
            $this->checkMessageIsActivated(BedFrameTooLargeProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $data = $this->data[BedFrameTooLargeProcessor::ENTRY_NAME];

        $order = new Order();

        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn('bed-frame');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TEDSO140190_TEDPBOIS15');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(1, count($order->getMessages()));
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }

    public function testBundleBedFrameTooLarge(): void
    {
        try {
            $this->checkMessageIsActivated(BundleBedFrameTooLargeProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $data = $this->data[BundleBedFrameTooLargeProcessor::ENTRY_NAME];

        $order = new Order();

        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn('bed-frame');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('BUNDLET1_BEIGE140200_SYNTH5070_BOIS20');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(1, count($order->getMessages()));
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }

    public function testBundleComboBedMonobloc(): void
    {
        try {
            $this->checkMessageIsActivated(BundleComboBedMonoblocProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $data = $this->data[BundleComboBedMonoblocProcessor::ENTRY_NAME];

        $order = new Order();

        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn('bed-frame');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('BUNDLE_LIT1_BEIGE140200_BOIS20');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(1, count($order->getMessages()));
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }

    public function testBundleComboBedMonoblocAndStorageBedFrame(): void
    {
        try {
            $this->checkMessageIsActivated(BundleComboBedStorageProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $data = $this->data[BundleComboBedStorageProcessor::ENTRY_NAME];

        $order = new Order();

        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn('bed-frame');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('BUNDLE_LIT2_BEIGE180200_STANDARD');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(1, count($order->getMessages()));
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }

    public function testVoluminousProductOnCart(): void
    {
        try {
            $this->checkMessageIsActivated(VoluminousProductOnCartProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $data = $this->data[VoluminousProductOnCartProcessor::ENTRY_NAME];

        $order = new Order();

        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn('storage-bed-frame');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('NOT IMPORTANT');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(1, count($order->getMessages()));
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }

    public function testVoluminousProductVariantOnCart(): void
    {
        try {
            $this->checkMessageIsActivated(VoluminousProductOnCartProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $data = $this->data[VoluminousProductOnCartProcessor::ENTRY_NAME];

        $order = new Order();

        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn('NOT IMPORTANT');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TEDSO140190_TEDPBOIS15');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(1, count($order->getMessages()));
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }

    public function testVoluminousProductAndProductVariantOnCartButOnlyOneMessage(): void
    {
        try {
            $this->checkMessageIsActivated(VoluminousProductOnCartProcessor::ENTRY_NAME);
        } catch (\UnexpectedValueException $e) {
            self::markTestSkipped($e->getMessage());
        }

        $data = $this->data[VoluminousProductOnCartProcessor::ENTRY_NAME];

        $order = new Order();

        $product = $this->createMock(Product::class);
        $product->method('getCode')->willReturn('storage-bed-frame');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getCode')->willReturn('TEDSO140190_TEDPBOIS15');

        $orderItem = $this->createMock(OrderItem::class);
        $orderItem->method('getProduct')->willReturn($product);
        $orderItem->method('getVariant')->willReturn($productVariant);

        $order->addItem($orderItem);

        $this->processor->process($order);

        self::assertEquals(1, count($order->getMessages()));
        self::assertEquals($data['messages']['message'], $order->getMessages()[0]->getMessage());
        self::assertEquals($data['messages']['type'], $order->getMessages()[0]->getType());
    }
}
