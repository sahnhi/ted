<?php

namespace App\Tests\Unit\Promotion\Checker;

use App\Promotion\Checker\VariantRuleChecker;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

class VariantRuleCheckerTest extends TestCase
{
    public const TEST_ELIGIBLE_GET_CODE = '45';
    public const TEST_NOT_ELIGIBLE_GET_CODE = '33';

    private $subject;

    public function setUp(): void
    {
        // Constructor arguments
        $this->subject = $this->createMock(OrderInterface::class);
    }

    public function testIsEligible(): void
    {
        $productVariant = $this->createMock(ProductVariantInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $productVariant->expects($this->once())
            ->method('getCode')
            ->willReturn(self::TEST_ELIGIBLE_GET_CODE);

        $orderItem->expects($this->once())
            ->method('getVariant')
            ->willReturn($productVariant);

        $collection = $this->createMock(Collection::class);
        $collection->method('getIterator')
            ->willReturn(new \ArrayIterator([$orderItem]));

        $this->subject->expects($this->once())
            ->method('getItems')
            ->willReturn($collection);

        $variantRuleChecker = new VariantRuleChecker();
        $ret = $variantRuleChecker->isEligible($this->subject, ['product_variant_code' => '45']);

        $this->assertEquals($ret, true);
    }

    public function testIsNotEligible(): void
    {
        $productVariant = $this->createMock(ProductVariantInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $productVariant->expects($this->once())
            ->method('getCode')
            ->willReturn(self::TEST_NOT_ELIGIBLE_GET_CODE);

        $orderItem->expects($this->once())
            ->method('getVariant')
            ->willReturn($productVariant);

        $collection = $this->createMock(Collection::class);
        $collection->method('getIterator')
            ->willReturn(new \ArrayIterator([$orderItem]));

        $this->subject->expects($this->once())
            ->method('getItems')
            ->willReturn($collection);

        $variantRuleChecker = new VariantRuleChecker();
        $ret = $variantRuleChecker->isEligible($this->subject, ['product_variant_code' => '45']);

        $this->assertEquals($ret, false);
    }
}
