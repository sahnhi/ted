<?php

declare(strict_types=1);

namespace App\Tests\Unit\ApiPlatform\State\Provider\Product;

use App\ApiPlatform\State\Product\ProductOptionItemDataProvider;
use App\Doctrine\Entity\Sylius\ProductOption;
use App\Doctrine\Entity\Sylius\ProductOptionValue;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Product\Repository\ProductOptionRepositoryInterface;

class ProductOptionItemDataProviderTest extends TestCase
{
    private ProductOptionRepositoryInterface $productOptionRepository;

    private ProductOptionItemDataProvider $dataProvider;

    protected function setUp(): void
    {
        $this->productOptionRepository = $this->createMock(ProductOptionRepositoryInterface::class);
        $this->dataProvider = new ProductOptionItemDataProvider($this->productOptionRepository);
    }

    public function testGetItem(): void
    {
        $productOptionId = 'test_option';

        $productOption = new ProductOption();
        $productOption->setCode($productOptionId);

        $enabledValue = new ProductOptionValue();
        $enabledValue->setEnabled(true);

        $disabledValue = new ProductOptionValue();
        $disabledValue->setEnabled(false);

        $productOption->addValue($enabledValue);
        $productOption->addValue($disabledValue);

        $this->productOptionRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => $productOptionId])
            ->willReturn($productOption);

        $result = $this->dataProvider->getItem(ProductOption::class, $productOptionId);

        self::assertEquals($productOption, $result);
        self::assertCount(1, $productOption->getValues());
        self::assertSame($enabledValue, $productOption->getValues()->first());
    }

    public function testGetItemNotFound(): void
    {
        $productOptionId = 'test_option';

        $this->productOptionRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => $productOptionId])
            ->willReturn(null);

        $result = $this->dataProvider->getItem(ProductOption::class, $productOptionId);

        self::assertNull($result);
    }

    public function testSupports(): void
    {
        self::assertTrue($this->dataProvider->supports(ProductOption::class));
        self::assertFalse($this->dataProvider->supports('Foo'));
    }
}
