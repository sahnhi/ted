<?php

declare(strict_types=1);

namespace App\Tests\Unit\Resolver\Shipping;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Repository\Sylius\ShippingMethodRepository;
use App\Provider\Shipping\EligibleShippingMethodProvider;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use App\Resolver\Shipping\ShippingMethodCategoryBasedShippingMethodsResolver;
use App\Services\Calendar\ServiceRulesService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Shipping\Checker\Eligibility\ShippingMethodEligibilityCheckerInterface;

class ShippingMethodCategoryBasedShippingMethodsResolverTest extends TestCase
{
    private readonly ShippingMethodCategoryProvider $shippingMethodCategoryProvider;

    private readonly EligibleShippingMethodProvider $eligibleShippingMethodProvider;

    private readonly ShippingMethodCategoryBasedShippingMethodsResolver $resolver;

    protected function setUp(): void
    {
        $this->shippingMethodCategoryProvider = $this->createMock(ShippingMethodCategoryProvider::class);
        $this->eligibleShippingMethodProvider = $this->createMock(EligibleShippingMethodProvider::class);

        $this->resolver = new ShippingMethodCategoryBasedShippingMethodsResolver(
            $this->shippingMethodCategoryProvider,
            $this->eligibleShippingMethodProvider,
            $this->createMock(ZoneMatcherInterface::class),
            $this->createMock(ServiceRulesService::class),
            $this->createMock(ShippingMethodRepository::class),
            $this->createMock(ShippingMethodEligibilityCheckerInterface::class)
        );
    }

    public function testItReturnsAnEmptyArrayIfNoEligibleShippingMethodsAreFound(): void
    {
        $shipment = new Shipment();
        $order = new Order();
        $shipment->setOrder($order);

        $this->eligibleShippingMethodProvider
            ->expects($this->never())
            ->method('provideEligibleShippingMethods');

        $this->shippingMethodCategoryProvider
            ->expects($this->once())
            ->method('provideEligibleForOrder')
            ->with($order)
            ->willReturn([]);

        $shippingMethods = $this->resolver->getSupportedMethods($shipment);

        self::assertCount(0, $shippingMethods);
    }

    public function testItCorrectlyReturnsTheAvailableShippingMethods(): void
    {
        $shipment = new Shipment();
        $order = new Order();
        $shipment->setOrder($order);

        $firstShippingCategory = new ShippingMethodCategory();
        $firstShippingMethod = new ShippingMethod();
        $firstShippingCategory->addShippingMethod($firstShippingMethod);

        $secondShippingCategory = new ShippingMethodCategory();
        $secondShippingMethod = new ShippingMethod();
        $secondShippingCategory->addShippingMethod($secondShippingMethod);

        $this->eligibleShippingMethodProvider
            ->expects($this->never())
            ->method('provideEligibleShippingMethods');

        $this->shippingMethodCategoryProvider
            ->expects($this->once())
            ->method('provideEligibleForOrder')
            ->with($order)
            ->willReturn([$firstShippingCategory, $secondShippingCategory]);

        $shippingMethods = $this->resolver->getSupportedMethods($shipment);

        self::assertCount(2, $shippingMethods);
        self::assertSame([$firstShippingMethod, $secondShippingMethod], $shippingMethods);
    }

    public function testItUsesTheSelectedShippingMethodCategory(): void
    {
        $channel = new Channel();
        $shipment = new Shipment();
        $order = new Order();
        $order->addShipment($shipment);
        $order->setChannel($channel);

        $shippingMethodCategory = new ShippingMethodCategory();
        $shippingMethod = new ShippingMethod();
        $shippingMethodCategory->addShippingMethod($shippingMethod);

        $shipment->setShippingMethodCategory($shippingMethodCategory);

        $this->eligibleShippingMethodProvider
            ->expects($this->once())
            ->method('provideEligibleShippingMethods')
            ->with($shippingMethodCategory, $order->getShipments(), $channel)
            ->willReturn(new ArrayCollection([$shippingMethod]));

        $this->shippingMethodCategoryProvider
            ->expects($this->never())
            ->method('provideEligibleForOrder');

        $shippingMethods = $this->resolver->getSupportedMethods($shipment);

        self::assertCount(1, $shippingMethods);
        self::assertSame([$shippingMethod], $shippingMethods);
    }
}
