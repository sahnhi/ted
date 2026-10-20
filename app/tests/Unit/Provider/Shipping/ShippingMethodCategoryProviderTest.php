<?php

declare(strict_types=1);

namespace App\Tests\Unit\Provider\Shipping;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Doctrine\Entity\Sylius\Zone;
use App\Doctrine\Repository\ShippingMethodCategoryRepository;
use App\Factory\OrderFactoryInterface;
use App\Provider\Shipping\EligibleShippingMethodProvider;
use App\Provider\Shipping\ShippingMethodCategoryProvider;
use App\Resolver\Shipping\ShippingMethodCategoryServiceQuantityResolver;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Addressing\Matcher\ZoneMatcherInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Shipping\Calculator\DelegatingCalculatorInterface;
use Sylius\Component\Shipping\Checker\Eligibility\ShippingMethodEligibilityCheckerInterface;

class ShippingMethodCategoryProviderTest extends TestCase
{
    private readonly ShippingMethodCategoryRepository $shippingMethodCategoryRepository;

    private readonly EligibleShippingMethodProvider $eligibleShippingMethodProvider;

    private readonly ZoneMatcherInterface $zoneMatcher;

    private readonly OrderFactoryInterface $orderFactory;

    private readonly ShippingMethodCategoryProvider $provider;

    protected function setUp(): void
    {
        $this->shippingMethodCategoryRepository = $this->createMock(ShippingMethodCategoryRepository::class);
        $this->shippingMethodEligibilityChecker = $this->createMock(ShippingMethodEligibilityCheckerInterface::class);
        $this->shippingChargesCalculator = $this->createMock(DelegatingCalculatorInterface::class);
        $this->zoneMatcher = $this->createMock(ZoneMatcherInterface::class);
        $this->orderFactory = $this->createMock(OrderFactoryInterface::class);
        $this->eligibleShippingMethodProvider = $this->createMock(EligibleShippingMethodProvider::class);

        $this->provider = new ShippingMethodCategoryProvider(
            $this->shippingMethodCategoryRepository,
            $this->eligibleShippingMethodProvider,
            $this->zoneMatcher,
            $this->orderFactory,
            $this->createMock(ShippingMethodCategoryServiceQuantityResolver::class),
        );
    }

    public function testItProvidesCategoriesThatHaveEligibleShippingMethodsForOrder(): void
    {
        $channel = new Channel();
        $address = new Address();

        $order = $this->createMock(Order::class);
        $order->expects($this->once())->method('getChannel')->willReturn($channel);
        $order->expects($this->once())->method('getShippingAddress')->willReturn($address);

        $zone = new Zone();

        $this->zoneMatcher
            ->expects($this->once())
            ->method('matchAll')
            ->with($address, Scope::SHIPPING)
            ->willReturn([$zone]);

        $shipment = new Shipment();
        $order
            ->expects($this->once())
            ->method('getShipments')
            ->willReturn(new ArrayCollection([$shipment]));

        $firstCategory = new ShippingMethodCategory();
        $firstCategory->setCode('first-shipping-method-category');
        $firstShippingMethod = new ShippingMethod();
        $firstCategory->addShippingMethod($firstShippingMethod);

        $secondCategory = new ShippingMethodCategory();
        $secondCategory->setCode('second-shipping-method-category');
        $secondShippingMethod = new ShippingMethod();
        $secondCategory->addShippingMethod($secondShippingMethod);

        $this->eligibleShippingMethodProvider
            ->expects($this->exactly(2))
            ->method('provideEligibleShippingMethods')
            ->willReturnOnConsecutiveCalls(new ArrayCollection([$firstShippingMethod]), new ArrayCollection([]));

        $this->shippingMethodCategoryRepository
            ->expects($this->once())
            ->method('findEnabledForZonesAndChannel')
            ->with([$zone], $channel)
            ->willReturn([$firstCategory, $secondCategory]);

        $this->orderFactory
            ->expects($this->never())
            ->method('createNewForChargesEstimate');

        $shippingMethodCategories = $this->provider->provideEligibleForOrder($order);

        self::assertCount(1, $shippingMethodCategories);
        self::assertEquals('first-shipping-method-category', $shippingMethodCategories['first-shipping-method-category']->getCode());
        self::assertCount(1, $shippingMethodCategories['first-shipping-method-category']->getShippingMethods());
        self::assertSame($firstShippingMethod, $shippingMethodCategories['first-shipping-method-category']->getShippingMethods()->first());
    }
}
