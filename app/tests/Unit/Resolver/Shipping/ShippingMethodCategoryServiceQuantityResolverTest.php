<?php

declare(strict_types=1);

namespace App\Tests\Unit\Resolver\Shipping;

use App\Doctrine\Entity\BundleAssociation;
use App\Doctrine\Entity\ShippingMethodCategoryService;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Resolver\Shipping\ShippingMethodCategoryServiceQuantityResolver;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ShippingMethodCategoryServiceQuantityResolverTest extends TestCase
{
    private ShippingMethodCategoryServiceQuantityResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ShippingMethodCategoryServiceQuantityResolver();
    }

    public function testResolveServiceQuantityWithEmptyOrder(): void
    {
        $order = $this->createMock(Order::class);
        $order
            ->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection());

        $service = $this->createMock(ShippingMethodCategoryService::class);
        $service
            ->expects($this->never())
            ->method('getRestrictedProducts')
            ->willReturn(new ArrayCollection());

        $this->assertSame(0, $this->resolver->resolveServiceQuantity($service, $order));
    }

    public function testResolveServiceQuantityWithNonBundleProduct(): void
    {
        $restrictedProduct = $this->createMock(Product::class);

        $service = $this->createMock(ShippingMethodCategoryService::class);
        $service
            ->expects($this->once())
            ->method('getRestrictedProducts')
            ->willReturn(new ArrayCollection([$restrictedProduct]));

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant
            ->expects($this->once())
            ->method('isBundle')
            ->willReturn(false);

        $productVariant
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($restrictedProduct);

        $orderItem = $this->createConfiguredMock(OrderItem::class, [
            'getQuantity' => 2,
            'getVariant' => $productVariant,
        ]);

        $order = $this->createMock(Order::class);
        $order
            ->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$orderItem]));

        $this->assertSame(2, $this->resolver->resolveServiceQuantity($service, $order));
    }

    public function testResolveServiceQuantityWithRestrictedProduct(): void
    {
        $restrictedProduct = $this->createMock(Product::class);

        $service = $this->createMock(ShippingMethodCategoryService::class);
        $service
            ->expects($this->once())
            ->method('getRestrictedProducts')
            ->willReturn(new ArrayCollection([$restrictedProduct]));

        $variantWithRestrictedProduct = $this->createMock(ProductVariant::class);
        $variantWithRestrictedProduct
            ->expects($this->once())
            ->method('isBundle')
            ->willReturn(false);

        $variantWithRestrictedProduct
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($restrictedProduct);

        $orderItem1 = $this->createConfiguredMock(OrderItem::class, [
            'getQuantity' => 3,
            'getVariant' => $variantWithRestrictedProduct,
        ]);

        $variantWithoutRestrictedProduct = $this->createMock(ProductVariant::class);
        $variantWithoutRestrictedProduct
            ->expects($this->once())
            ->method('isBundle')
            ->willReturn(false);

        $variantWithoutRestrictedProduct
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->createMock(Product::class));

        $orderItem2 = $this->createConfiguredMock(OrderItem::class, [
            'getQuantity' => 10,
            'getVariant' => $variantWithoutRestrictedProduct,
        ]);

        $order = $this->createMock(Order::class);
        $order->method('getItems')->willReturn(new ArrayCollection([$orderItem1, $orderItem2]));

        $this->assertSame(3, $this->resolver->resolveServiceQuantity($service, $order));
    }

    public function testResolveServiceQuantityWithBundleProduct(): void
    {
        $restrictedProduct = $this->createMock(Product::class);

        $service = $this->createMock(ShippingMethodCategoryService::class);
        $service
            ->expects($this->once())
            ->method('getRestrictedProducts')
            ->willReturn(new ArrayCollection([$restrictedProduct]));

        $bundleAssociationWithRestrictedProduct = $this->createConfiguredMock(BundleAssociation::class, [
            'getQuantity' => 2,
            'getVariant' => $this->createConfiguredMock(ProductVariant::class, [
                'getProduct' => $restrictedProduct,
            ]),
        ]);

        $bundleAssociationWithoutRestrictedProduct = $this->createConfiguredMock(BundleAssociation::class, [
            'getQuantity' => 20,
            'getVariant' => $this->createConfiguredMock(ProductVariant::class, [
                'getProduct' => $this->createMock(Product::class),
            ]),
        ]);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant
            ->expects($this->once())
            ->method('isBundle')
            ->willReturn(true);

        $productVariant
            ->expects($this->once())
            ->method('getBundleAssociations')
            ->willReturn(new ArrayCollection([$bundleAssociationWithRestrictedProduct, $bundleAssociationWithoutRestrictedProduct]));

        $orderItem = $this->createConfiguredMock(OrderItem::class, [
            'getQuantity' => 2,
            'getVariant' => $productVariant,
        ]);

        $order = $this->createMock(Order::class);
        $order
            ->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$orderItem]));

        $this->assertSame(4, $this->resolver->resolveServiceQuantity($service, $order));
    }

    public function testResolveServiceQuantityWithBundleAndNonBundleProducts(): void
    {
        $restrictedProduct = $this->createMock(Product::class);

        $service = $this->createMock(ShippingMethodCategoryService::class);
        $service
            ->expects($this->once())
            ->method('getRestrictedProducts')
            ->willReturn(new ArrayCollection([$restrictedProduct]));

        $bundleVariant = $this->createMock(ProductVariant::class);
        $bundleVariant
            ->expects($this->once())
            ->method('isBundle')
            ->willReturn(true);

        $bundleAssociationWithRestrictedProduct = $this->createConfiguredMock(BundleAssociation::class, [
            'getQuantity' => 2,
            'getVariant' => $this->createConfiguredMock(ProductVariant::class, [
                'getProduct' => $restrictedProduct,
            ]),
        ]);

        $bundleVariant
            ->expects($this->once())
            ->method('getBundleAssociations')
            ->willReturn(new ArrayCollection([$bundleAssociationWithRestrictedProduct]));

        $bundleItem = $this->createConfiguredMock(OrderItem::class, [
            'getQuantity' => 2,
            'getVariant' => $bundleVariant,
        ]);

        $nonBundleVariant = $this->createMock(ProductVariant::class);
        $nonBundleVariant
            ->expects($this->once())
            ->method('isBundle')
            ->willReturn(false);

        $nonBundleVariant
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($restrictedProduct);

        $nonBundleItem = $this->createConfiguredMock(OrderItem::class, [
            'getQuantity' => 3,
            'getVariant' => $nonBundleVariant,
        ]);

        $order = $this->createMock(Order::class);
        $order
            ->expects($this->once())
            ->method('getItems')
            ->willReturn(new ArrayCollection([$bundleItem, $nonBundleItem]));

        // 7 = (Bundle quantity of 2 * Order Item Quantity of 2) + Order Item quantity of 3
        $this->assertSame(7, $this->resolver->resolveServiceQuantity($service, $order));
    }
}
