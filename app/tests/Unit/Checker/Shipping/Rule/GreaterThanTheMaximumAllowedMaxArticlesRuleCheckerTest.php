<?php

declare(strict_types=1);

namespace App\Tests\Unit\Checker\Shipping\Rule;

use App\Checker\Shipping\Rule\GreaterThanTheMaximumAllowedMaxArticlesRuleChecker;
use App\Checker\Shipping\Rule\LowerOrEqualThanTheMaximumAllowedMaxArticlesRuleChecker;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Form\Extension\ShippingMethodRuleTypeExtension;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Repository\ShippingMethodRepositoryInterface;
use Sylius\Component\Shipping\Checker\Rule\RuleCheckerInterface;

class GreaterThanTheMaximumAllowedMaxArticlesRuleCheckerTest extends TestCase
{
    private readonly ShippingMethodRepositoryInterface $shippingMethodRepository;

    private readonly RuleCheckerInterface $checker;

    protected function setUp(): void
    {
        $this->checker = new GreaterThanTheMaximumAllowedMaxArticlesRuleChecker(
            new LowerOrEqualThanTheMaximumAllowedMaxArticlesRuleChecker(
                $this->shippingMethodRepository = $this->createMock(ShippingMethodRepositoryInterface::class)
            )
        );
    }

    public function testItReturnsFalseIfTheShipmentHasLessArticlesThanAllowed(): void
    {
        $shipment = $this->createMock(Shipment::class);

        $method = new ShippingMethod();
        $method->setMaxArticles(10);

        $shipment
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);

        $firstVariant = new ProductVariant();
        $firstProduct = new Product();
        $firstProduct->setWeighting(4);
        $firstVariant->setProduct($firstProduct);

        $secondVariant = new ProductVariant();
        $secondProduct = new Product();
        $secondProduct->setWeighting(4);
        $secondVariant->setProduct($secondProduct);

        $orderItemUnit1 = $this->createMock(OrderItemUnit::class);
        $orderItemUnit1
            ->expects($this->once())
            ->method('getShippable')
            ->willReturn($firstVariant);

        $orderItemUnit2 = $this->createMock(OrderItemUnit::class);
        $orderItemUnit2
            ->expects($this->once())
            ->method('getShippable')
            ->willReturn($secondVariant);

        $shipment
            ->expects($this->once())
            ->method('getUnits')
            ->willReturn(new ArrayCollection([$orderItemUnit1, $orderItemUnit2]));

        // 4 + 4 = 8, which is less than the allowed 10
        self::assertFalse($this->checker->isEligible($shipment, []));
    }

    public function testItReturnsFalseIfTheShipmentHasTheSameAmountOfArticlesThanAllowed(): void
    {
        $shipment = $this->createMock(Shipment::class);

        $method = new ShippingMethod();
        $method->setMaxArticles(10);

        $shipment
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);

        $firstVariant = new ProductVariant();
        $firstProduct = new Product();
        $firstProduct->setWeighting(4);
        $firstVariant->setProduct($firstProduct);

        $secondVariant = new ProductVariant();
        $secondProduct = new Product();
        $secondProduct->setWeighting(6);
        $secondVariant->setProduct($secondProduct);

        $orderItemUnit1 = $this->createMock(OrderItemUnit::class);
        $orderItemUnit1
            ->expects($this->once())
            ->method('getShippable')
            ->willReturn($firstVariant);

        $orderItemUnit2 = $this->createMock(OrderItemUnit::class);
        $orderItemUnit2
            ->expects($this->once())
            ->method('getShippable')
            ->willReturn($secondVariant);

        $shipment
            ->expects($this->once())
            ->method('getUnits')
            ->willReturn(new ArrayCollection([$orderItemUnit1, $orderItemUnit2]));

        self::assertFalse($this->checker->isEligible($shipment, []));
    }

    public function testItReturnsTrueIfTheShipmentHasAGreaterNumberOfArticlesThanAllowed(): void
    {
        $shipment = $this->createMock(Shipment::class);

        $method = new ShippingMethod();
        $method->setMaxArticles(10);

        $shipment
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);

        $firstVariant = new ProductVariant();
        $firstProduct = new Product();
        $firstProduct->setWeighting(10);
        $firstVariant->setProduct($firstProduct);

        $secondVariant = new ProductVariant();
        $secondProduct = new Product();
        $secondProduct->setWeighting(1);
        $secondVariant->setProduct($secondProduct);

        $orderItemUnit1 = $this->createMock(OrderItemUnit::class);
        $orderItemUnit1
            ->expects($this->once())
            ->method('getShippable')
            ->willReturn($firstVariant);

        $orderItemUnit2 = $this->createMock(OrderItemUnit::class);
        $orderItemUnit2
            ->expects($this->once())
            ->method('getShippable')
            ->willReturn($secondVariant);

        $shipment
            ->expects($this->once())
            ->method('getUnits')
            ->willReturn(new ArrayCollection([$orderItemUnit1, $orderItemUnit2]));

        self::assertTrue($this->checker->isEligible($shipment, []));
    }

    public function testItDoesNotUsesTheShipmentShippingMethodIfTheConfigurationHasTheShippingMethodCode(): void
    {
        $shipment = $this->createMock(Shipment::class);

        $method = new ShippingMethod();
        $method->setMaxArticles(10);

        $this->shippingMethodRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'DPD'])
            ->willReturn($method);

        $shipment
            ->expects($this->never())
            ->method('getMethod');

        $firstVariant = new ProductVariant();
        $firstProduct = new Product();
        $firstProduct->setWeighting(10);
        $firstVariant->setProduct($firstProduct);

        $secondVariant = new ProductVariant();
        $secondProduct = new Product();
        $secondProduct->setWeighting(1);
        $secondVariant->setProduct($secondProduct);

        $orderItemUnit1 = $this->createMock(OrderItemUnit::class);
        $orderItemUnit1
            ->expects($this->once())
            ->method('getShippable')
            ->willReturn($firstVariant);

        $orderItemUnit2 = $this->createMock(OrderItemUnit::class);
        $orderItemUnit2
            ->expects($this->once())
            ->method('getShippable')
            ->willReturn($secondVariant);

        $shipment
            ->expects($this->once())
            ->method('getUnits')
            ->willReturn(new ArrayCollection([$orderItemUnit1, $orderItemUnit2]));

        self::assertTrue($this->checker->isEligible($shipment, [
            ShippingMethodRuleTypeExtension::SHIPPING_METHOD_CODE_INDEX => 'DPD',
        ]));
    }
}
