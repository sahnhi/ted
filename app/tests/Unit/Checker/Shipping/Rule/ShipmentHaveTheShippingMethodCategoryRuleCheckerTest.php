<?php

declare(strict_types=1);

namespace App\Tests\Unit\Checker\Shipping\Rule;

use App\Doctrine\Entity\ShippingMethodCategory;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Promotion\Checker\Rule\ShipmentHaveTheShippingMethodCategoryRuleChecker;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

class ShipmentHaveTheShippingMethodCategoryRuleCheckerTest extends TestCase
{
    private ShipmentHaveTheShippingMethodCategoryRuleChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new ShipmentHaveTheShippingMethodCategoryRuleChecker();
    }

    public function testIsEligibleReturnsFalseIfConfigurationIsEmpty(): void
    {
        $subject = $this->createMock(OrderInterface::class);
        $configuration = [];

        $result = $this->checker->isEligible($subject, $configuration);

        $this->assertFalse($result);
    }

    public function testIsEligibleThrowsExceptionForUnsupportedSubject(): void
    {
        $subject = $this->createMock(PromotionSubjectInterface::class);
        $configuration = ['shippingMethodCategory' => 'category1'];

        $this->expectException(UnsupportedTypeException::class);

        $this->checker->isEligible($subject, $configuration);
    }

    public function testIsEligibleReturnsTrueWhenShipmentCategoryMatches(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $shippingMethodCategory = $this->createMock(ShippingMethodCategory::class);
        $shippingMethodCategory
            ->method('getCode')
            ->willReturn('category1');

        $shipment = $this->createMock(Shipment::class);
        $shipment
            ->expects($this->once())
            ->method('getShippingMethodCategory')
            ->willReturn($shippingMethodCategory);

        $order
            ->method('getShipments')
            ->willReturn(new ArrayCollection([$shipment]));

        $this->assertTrue($this->checker->isEligible($order, ['shippingMethodCategory' => 'category1']));
    }

    public function testIsEligibleReturnsTrueWhenShippingMethodCategoryMatches(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $shippingMethodCategory = $this->createMock(ShippingMethodCategory::class);
        $shippingMethodCategory
            ->method('getCode')
            ->willReturn('category1');

        $method = $this->createMock(ShippingMethod::class);
        $method
            ->expects($this->once())
            ->method('getShippingMethodCategory')
            ->willReturn($shippingMethodCategory);

        $shipment = $this->createMock(Shipment::class);
        $shipment
            ->expects($this->once())
            ->method('getShippingMethodCategory')
            ->willReturn(null);

        $shipment
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);

        $order
            ->expects($this->once())
            ->method('getShipments')
            ->willReturn(new ArrayCollection([$shipment]));

        $this->assertTrue($this->checker->isEligible($order, ['shippingMethodCategory' => 'category1']));
    }

    public function testIsEligibleReturnsFalseWhenNoMatchFound(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $shipment = $this->createMock(Shipment::class);
        $method = $this->createMock(ShippingMethod::class);

        $shippingMethodCategory = $this->createMock(ShippingMethodCategory::class);
        $shippingMethodCategory
            ->method('getCode')
            ->willReturn('category2');

        $shipment
            ->expects($this->once())
            ->method('getShippingMethodCategory')
            ->willReturn($shippingMethodCategory);

        $method
            ->expects($this->once())
            ->method('getShippingMethodCategory')
            ->willReturn($shippingMethodCategory);

        $shipment
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);

        $order
            ->expects($this->once())
            ->method('getShipments')
            ->willReturn(new ArrayCollection([$shipment]));

        $this->assertFalse($this->checker->isEligible($order, ['shippingMethodCategory' => 'category1']));
    }
}
