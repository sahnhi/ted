<?php

declare(strict_types=1);

namespace App\Tests\Unit\ApiPlatform\State\Provider\Product;

use App\ApiPlatform\State\Product\ProductItemDataProvider;
use App\ApiPlatform\State\Product\ProductItemDataProviderInterface;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductOption;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Product\Resolver\AvailableProductOptionValuesResolverInterface;

class ProductItemDataProviderTest extends TestCase
{
    private readonly ProductItemDataProvider $provider;

    private readonly ProductItemDataProviderInterface $decoratedProductItemDataProvider;

    private readonly AvailableProductOptionValuesResolverInterface $availableProductOptionValuesResolver;

    protected function setUp(): void
    {
        $this->decoratedProductItemDataProvider = $this->createMock(ProductItemDataProviderInterface::class);
        $this->availableProductOptionValuesResolver = $this->createMock(AvailableProductOptionValuesResolverInterface::class);

        $this->provider = new ProductItemDataProvider(
            $this->decoratedProductItemDataProvider,
            $this->availableProductOptionValuesResolver,
        );
    }

    public function testGetItem(): void
    {
        $productId = 'mattress';

        $product = new Product();
        $product->setCode('mattress');

        $enabledValue = new ProductOptionValue();
        $enabledValue->setEnabled(true);

        $enabledProductOption = $this->createMock(ProductOption::class);
        $enabledProductOption
            ->expects($this->once())
            ->method('getEnabledValues')
            ->willReturn(new ArrayCollection([$enabledValue]));

        $disabledOption = $this->createMock(ProductOption::class);
        $disabledOption
            ->expects($this->once())
            ->method('getEnabledValues')
            ->willReturn(new ArrayCollection());

        $product->addOption($enabledProductOption);
        $product->addOption($disabledOption);

        $this->decoratedProductItemDataProvider
            ->expects($this->once())
            ->method('getItem')
            ->with(Product::class, $productId)
            ->willReturn($product);

        $this->availableProductOptionValuesResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($product, $enabledProductOption)
            ->willReturn(new ArrayCollection([$enabledValue]));

        $result = $this->provider->getItem(Product::class, $productId);

        self::assertSame($product, $result);
        self::assertCount(1, $result->getOptions());
        self::assertSame($enabledProductOption, $result->getOptions()->first());
    }

    public function testGetItemNotFound(): void
    {
        $productId = 'mattress';

        $this->decoratedProductItemDataProvider
            ->expects($this->once())
            ->method('getItem')
            ->with(Product::class, $productId)
            ->willReturn(null);

        self::assertNull($this->provider->getItem(Product::class, $productId));
    }

    public function testSupports(): void
    {
        $this->decoratedProductItemDataProvider
            ->expects($this->exactly(2))
            ->method('supports')
            ->with(Product::class)
            ->willReturnOnConsecutiveCalls(true, false);

        self::assertTrue($this->provider->supports(Product::class));
        self::assertFalse($this->provider->supports(Product::class));
    }
}
