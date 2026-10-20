<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler\Shipping;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Repository\ShippingMethodCategoryRepository;
use App\Enum\AdjustmentTypeEnum;
use App\MessageHandler\Shipping\SelectShippingMethodCategoryHandler;
use App\Model\SelectShippingMethodCategory;
use App\Modifier\Shipping\ShippingMethodCategoryServiceOrderModifier;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use Doctrine\Common\Collections\ArrayCollection;
use SM\SMException;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SelectShippingMethodCategoryHandlerTest extends KernelTestCase
{
    private readonly SelectShippingMethodCategoryHandler $handler;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->handler = new SelectShippingMethodCategoryHandler(
            self::$container->get('sylius.repository.order'),
            self::$container->get('sm.factory'),
            self::$container->get(ShippingMethodCategoryProvider::class),
            self::$container->get(ShippingMethodCategoryServiceOrderModifier::class),
            self::$container->get(ShippingMethodCategoryRepository::class),
            self::$container->get(OrderProcessorInterface::class),
        );
    }

    /**
     * @throws SMException
     */
    public function testItThrowsAnExceptionIfNoCartIsFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cart has not been found.');

        $this->handler->__invoke(new SelectShippingMethodCategory('RANDOM_CODE', [], 'invalid-order-token'));
    }

    /**
     * @throws SMException
     */
    public function testItThrowsAnExceptionIfTheOrderCanNotHaveAShippingMethodAssigned(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order cannot have shipment method assigned.');

        $this->handler->__invoke(new SelectShippingMethodCategory('RANDOM_CODE', [], '5811cf75-84a3-4673-9cc3-69755d1b72aa'));
    }

    public function testItThrowsAnExceptionIfTheSelectedShippingMethodCategoryIsNotEligibleForTheCart(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Shipping method category UNELIGIBLE_SHIPPING_METHOD_CODE is not eligible for this cart.');

        $this->handler->__invoke(
            new SelectShippingMethodCategory('UNELIGIBLE_SHIPPING_METHOD_CODE', [], '5811cf75-84a3-4673-9cc3-69755d1b72ad')
        );
    }

    public function testItCorrectlySetsTheShippingMethodCategoryOnTheOrderShipments(): void
    {
        $order = $this->handler->__invoke(
            new SelectShippingMethodCategory('ECONOMIQUE', [], '5811cf75-84a3-4673-9cc3-69755d1b72ad')
        );

        $shipments = $order->getShipments();

        self::assertCount(1, $shipments);

        /** @var Shipment $shipment */
        $shipment = $shipments->first();

        self::assertSame('ECONOMIQUE', $shipment->getShippingMethodCategory()->getCode());

        // Was 2024-11-20 10:00:00 before the handler was called
        self::assertNull($shipment->getStartDeliverySlot());
        // Was 2024-11-20 11:00:00 before the handler was called
        self::assertNull($shipment->getEndDeliverySlot());
    }

    /**
     * @throws SMException
     */
    public function testItCorrectlySetsTheIncludedServices(): void
    {
        /** @var Order $order */
        $order = self::$container->get('sylius.repository.order')->findCartByTokenValue('5811cf75-84a3-4673-9cc3-69755d1b72ad');

        self::assertCount(1, $order->getItems());

        $order = $this->handler->__invoke(
            new SelectShippingMethodCategory('ECONOMIQUE', [], '5811cf75-84a3-4673-9cc3-69755d1b72ad')
        );

        /** @var ArrayCollection<string, OrderItem> $items */
        $items = $order->getItems();

        self::assertCount(2, $items);

        $serviceItem = $items
            ->filter(static fn (OrderItem $item): bool => 'recuperation_variant' === $item->getVariant()->getCode())
            ->first();

        self::assertInstanceOf(OrderItem::class, $serviceItem);
        self::assertSame('recuperation_variant', $serviceItem->getVariant()->getCode());
        self::assertCount(1, $serviceItem->getAdjustments(AdjustmentTypeEnum::OFFERED_SERVICE->value));
        self::assertEquals(-1000, $serviceItem->getAdjustmentsTotal(AdjustmentTypeEnum::OFFERED_SERVICE->value));
        self::assertEquals(1, $serviceItem->getQuantity());
        self::assertSame(0, $serviceItem->getTotal());
    }

    /**
     * @throws SMException
     */
    public function testItCorrectlySetsTheSelectedServices(): void
    {
        /** @var Order $order */
        $order = self::$container->get('sylius.repository.order')->findCartByTokenValue('5811cf75-84a3-4673-9cc3-69755d1b72ad');

        self::assertCount(1, $order->getItems());

        $order = $this->handler->__invoke(
            new SelectShippingMethodCategory(
                'ECONOMIQUE',
                ['ECO_ASSEMBLY', 'ECO_ASSEMBLY', 'THIS_SERVICE_DOES_NOT_EXISTS'],
                '5811cf75-84a3-4673-9cc3-69755d1b72ad'
            )
        );

        /** @var ArrayCollection<string, OrderItem> $items */
        $items = $order->getItems();

        // The size here is 3 since the included service was also added
        self::assertCount(3, $items);

        $serviceItem = $items
            ->filter(static fn (OrderItem $item): bool => 'assembly_variant' === $item->getVariant()->getCode())
            ->first();

        self::assertInstanceOf(OrderItem::class, $serviceItem);
        self::assertSame('assembly_variant', $serviceItem->getVariant()->getCode());
        self::assertEquals(2, $serviceItem->getQuantity());
        self::assertSame(2000, $serviceItem->getTotal());
    }
}
