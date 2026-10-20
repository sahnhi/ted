<?php

declare(strict_types=1);

namespace App\Tests\Unit\ApiPlatform\State\Provider\Product;

use App\ApiPlatform\State\Product\ProductVariantItemDataProvider;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use App\Doctrine\Entity\Sylius\ProductVariant;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ProductVariantItemDataProviderTest extends TestCase
{
    private readonly ProductVariantRepositoryInterface $productVariantRepository;

    private readonly ProductVariantItemDataProvider $productVariantItemDataProvider;

    protected function setUp(): void
    {
        $this->productVariantRepository = $this->createMock(ProductVariantRepositoryInterface::class);

        $this->productVariantItemDataProvider = new ProductVariantItemDataProvider($this->productVariantRepository);
    }

    public function testGetItem(): void
    {
        $code = 'my-variant';

        $productVariant = new ProductVariant();

        $enabledValue = new ProductOptionValue();
        $enabledValue->setEnabled(true);

        $disabledValue = new ProductOptionValue();
        $disabledValue->setEnabled(false);

        $productVariant->addOptionValue($enabledValue);
        $productVariant->addOptionValue($disabledValue);

        $this->productVariantRepository
            ->expects($this->once())
            ->method('findOneEnabledByCode')
            ->with($code)
            ->willReturn($productVariant);

        $result = $this->productVariantItemDataProvider->getItem(ProductVariant::class, $code);

        self::assertInstanceOf(ProductVariant::class, $result);
        self::assertSame($productVariant, $result);
        self::assertCount(1, $result->getOptionValues());
        self::assertSame($enabledValue, $result->getOptionValues()->first());
    }

    public function testGetItemNotFound(): void
    {
        $code = 'not-found-variant';

        $this->productVariantRepository
            ->expects($this->once())
            ->method('findOneEnabledByCode')
            ->with($code)
            ->willReturn(null);

        $result = $this->productVariantItemDataProvider->getItem(ProductVariant::class, $code);

        self::assertNull($result);
    }

    public function testSupports(): void
    {
        self::assertTrue($this->productVariantItemDataProvider->supports(ProductVariant::class));
        self::assertFalse($this->productVariantItemDataProvider->supports('ARandomClass'));
    }
}
