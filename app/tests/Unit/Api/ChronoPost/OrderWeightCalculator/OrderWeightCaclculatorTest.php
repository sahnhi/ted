<?php

declare(strict_types=1);

namespace App\Tests\Unit\Api\ChronoPost\OrderWeightCalculator;

use App\Api\ChronoPost\Exception\OrderCannotWeightZeroGram;
use App\Api\ChronoPost\OrderWeightCalculator\OrderWeightCalculator;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Doctrine\Repository\Sylius\ProductVariantRepositoryInterface;
use App\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class OrderWeightCaclculatorTest extends TestCase
{
    /** @var OrderRepositoryInterface|MockObject */
    private $orderRepository;
    /** @var ProductVariantRepositoryInterface|(ProductVariantRepositoryInterface&MockObject)|MockObject */
    private $productVariantRepository;
    private OrderWeightCalculator $orderWeightCalculator;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->productVariantRepository = $this->createMock(ProductVariantRepositoryInterface::class);

        $this->orderWeightCalculator = new OrderWeightCalculator(
            $this->orderRepository,
            $this->productVariantRepository
        );
    }

    public function testCommandNotFound(): void
    {
        $this->orderRepository->expects(self::once())
            ->method('findTotalWeightInKilograms')
            ->with('tokenOrder')
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('command not found');

        $this->orderWeightCalculator->weightInGramsCalculateFromTokenOrder('tokenOrder');
    }

    public function testOrderWeightZeroGram(): void
    {
        $this->orderRepository->expects(self::once())
            ->method('findTotalWeightInKilograms')
            ->with('asdfg')
            ->willReturn('0');

        $this->expectException(OrderCannotWeightZeroGram::class);
        $this->expectExceptionMessage('The weight of the order is 0 for order with token asdfg');

        $this->orderWeightCalculator->weightInGramsCalculateFromTokenOrder('asdfg');
    }

    public function testCalculWeightInGramsFromLessThanAKilogram(): void
    {
        $this->orderRepository->expects(self::once())
            ->method('findTotalWeightInKilograms')
            ->with('tokenOrder')
            ->willReturn('0.1');

        $weighInGram = $this->orderWeightCalculator->weightInGramsCalculateFromTokenOrder('tokenOrder');

        $this->assertSame(100, $weighInGram);
    }

    public function testCalculWeightInGrams(): void
    {
        $this->orderRepository->expects(self::once())
            ->method('findTotalWeightInKilograms')
            ->with('tokenOrder')
            ->willReturn('1');

        $weighInGram = $this->orderWeightCalculator->weightInGramsCalculateFromTokenOrder('tokenOrder');

        $this->assertSame(1000, $weighInGram);
    }
}
