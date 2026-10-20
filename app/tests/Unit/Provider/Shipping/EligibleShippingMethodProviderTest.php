<?php

declare(strict_types=1);

namespace App\Tests\Unit\Provider\Shipping;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Provider\Shipping\EligibleShippingMethodProvider;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Shipping\Calculator\DelegatingCalculatorInterface;
use Sylius\Component\Shipping\Checker\Eligibility\ShippingMethodEligibilityCheckerInterface;

class EligibleShippingMethodProviderTest extends TestCase
{
    private readonly ShippingMethodEligibilityCheckerInterface $eligibilityChecker;

    private readonly DelegatingCalculatorInterface $shippingChargesCalculator;

    private readonly EligibleShippingMethodProvider $provider;

    protected function setUp(): void
    {
        $this->eligibilityChecker = $this->createMock(ShippingMethodEligibilityCheckerInterface::class);
        $this->shippingChargesCalculator = $this->createMock(DelegatingCalculatorInterface::class);

        $this->provider = new EligibleShippingMethodProvider(
            $this->eligibilityChecker,
            $this->shippingChargesCalculator
        );
    }

    public function testProvide(): void
    {
        $channel = new Channel();

        $shippingMethodCategory = new ShippingMethodCategory();
        $eligibleShippingMethod = new ShippingMethod();
        $eligibleShippingMethod->addChannel($channel);

        $ineligibleShippingMethod = $this->createMock(ShippingMethod::class);
        $ineligibleShippingMethod
            ->expects($this->once())
            ->method('hasChannel')
            ->with($channel)
            ->willReturn(false);

        $shippingMethodCategory
            ->addShippingMethod($eligibleShippingMethod)
            ->addShippingMethod($ineligibleShippingMethod);

        $shipment = $this->createMock(Shipment::class);
        $shipment
            ->method('setMethod')
            ->willReturnCallback(function (ShippingMethod $shippingMethod) use ($eligibleShippingMethod, $ineligibleShippingMethod): void {
                self::assertTrue($shippingMethod === $eligibleShippingMethod || $shippingMethod === $ineligibleShippingMethod);
            });

        $this->eligibilityChecker
            ->expects($this->once())
            ->method('isEligible')
            ->with($shipment, $eligibleShippingMethod)
            ->willReturn(true);

        $this->shippingChargesCalculator
            ->expects($this->once())
            ->method('calculate')
            ->willReturn(1000);

        $eligibleShippingMethods = $this->provider->provideEligibleShippingMethods($shippingMethodCategory, [$shipment], $channel);

        self::assertCount(1, $eligibleShippingMethods);
        self::assertSame($eligibleShippingMethods->first(), $eligibleShippingMethod);
        self::assertEquals(1000, $shippingMethodCategory->getLowestPrice());
    }
}
