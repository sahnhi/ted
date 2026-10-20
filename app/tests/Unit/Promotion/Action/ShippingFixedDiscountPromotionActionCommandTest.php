<?php

declare(strict_types=1);

namespace App\Tests\Unit\Promotion\Action;

use App\Promotion\Action\ShippingFixedDiscountPromotionActionCommand;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\AdjustmentInterface as OrderAdjustmentInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;

class ShippingFixedDiscountPromotionActionCommandTest extends TestCase
{
    private AdjustmentFactoryInterface $adjustmentFactory;
    private ShippingFixedDiscountPromotionActionCommand $command;
    private PromotionInterface $promotion;

    protected function setUp(): void
    {
        $this->adjustmentFactory = $this->createMock(AdjustmentFactoryInterface::class);
        $this->command = new ShippingFixedDiscountPromotionActionCommand($this->adjustmentFactory);
        $this->promotion = $this->createMock(PromotionInterface::class);
    }

    public function testExecuteWhenSubjectIsNotAnOrder(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $subject = $this->createMock(PromotionSubjectInterface::class);

        $this->command->execute($subject, ['amount' => 1000], $this->promotion);
    }

    public function testExecuteWhenAmountIsNotConfigured(): void
    {
        $subject = $this->createMock(OrderInterface::class);
        $subject
            ->expects($this->never())
            ->method('getShipments');

        $result = $this->command->execute($subject, [], $this->promotion);

        $this->assertFalse($result);
    }

    public function testExecuteWhenOrderHasNoShipments(): void
    {
        $subject = $this->createMock(OrderInterface::class);
        $subject
            ->expects($this->once())
            ->method('hasShipments')
            ->willReturn(false);

        $this->adjustmentFactory
            ->expects($this->never())
            ->method('createNew');

        $result = $this->command->execute($subject, ['amount' => 1000], $this->promotion);

        $this->assertFalse($result);
    }

    public function testExecuteAppliesDiscountCorrectly(): void
    {
        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment
            ->expects($this->exactly(2))
            ->method('getAdjustmentsTotal')
            ->willReturnOnConsecutiveCalls(2000, 0); // 'shipping' adjustment, 'order_shipping_promotion' adjustment

        $adjustment = $this->createMock(OrderAdjustmentInterface::class);
        $adjustment
            ->expects($this->once())
            ->method('setAmount')
            ->with(-1000);

        $this->adjustmentFactory
            ->method('createNew')
            ->willReturn($adjustment);

        $shipment
            ->expects($this->once())
            ->method('addAdjustment')
            ->with($adjustment);

        $subject = $this->createMock(OrderInterface::class);
        $subject->expects($this->once())->method('hasShipments')->willReturn(true);
        $subject->expects($this->once())->method('getShipments')->willReturn(new ArrayCollection([$shipment]));

        $result = $this->command->execute($subject, ['amount' => 1000], $this->promotion);

        $this->assertTrue($result);
    }

    public function testExecuteAppliesPartialDiscountWhenMaxDiscountIsLess(): void
    {
        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment
            ->expects($this->exactly(2))
            ->method('getAdjustmentsTotal')
            ->willReturnOnConsecutiveCalls(800, 0);

        $adjustment = $this->createMock(OrderAdjustmentInterface::class);
        $adjustment
            ->expects($this->once())
            ->method('setAmount')
            ->with(-800);

        $this->adjustmentFactory
            ->method('createNew')
            ->willReturn($adjustment);

        $shipment
            ->expects($this->once())
            ->method('addAdjustment')
            ->with($adjustment);

        $subject = $this->createMock(OrderInterface::class);
        $subject->expects($this->once())->method('hasShipments')->willReturn(true);
        $subject->expects($this->once())->method('getShipments')->willReturn(new ArrayCollection([$shipment]));

        $result = $this->command->execute($subject, ['amount' => 1000], $this->promotion);

        $this->assertTrue($result);
    }

    public function testRevertWhenSubjectIsNotAnOrder(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $subject = $this->createMock(PromotionSubjectInterface::class);

        $this->command->revert($subject, [], $this->promotion);
    }

    public function testRevertWhenOrderHasNoShipments(): void
    {
        $subject = $this->createMock(OrderInterface::class);
        $subject
            ->expects($this->once())
            ->method('hasShipments')
            ->willReturn(false);

        $subject
            ->expects($this->never())
            ->method('getAdjustments');

        $this->command->revert($subject, [], $this->promotion);
    }

    public function testRevertRemovesPromotionAdjustmentsFromOrder(): void
    {
        $adjustment = $this->createMock(OrderAdjustmentInterface::class);
        $adjustment
            ->expects($this->once())
            ->method('getOriginCode')
            ->willReturn('PROMO_CODE');

        $subject = $this->createMock(OrderInterface::class);

        $subject
            ->expects($this->once())
            ->method('hasShipments')
            ->willReturn(true);

        $subject
            ->expects($this->once())
            ->method('getAdjustments')
            ->with(AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$adjustment]));

        $subject
            ->expects($this->once())
            ->method('removeAdjustment')
            ->with($adjustment);

        $this->promotion
            ->method('getCode')
            ->willReturn('PROMO_CODE');

        $this->command->revert($subject, [], $this->promotion);
    }

    public function testRevertRemovesPromotionAdjustmentsFromShipments(): void
    {
        $shipment = $this->createMock(ShipmentInterface::class);
        $adjustment = $this->createMock(OrderAdjustmentInterface::class);

        $adjustment
            ->expects($this->once())
            ->method('getOriginCode')
            ->willReturn('PROMO_CODE');

        $shipment
            ->expects($this->once())
            ->method('getAdjustments')
            ->with(AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$adjustment]));

        $shipment
            ->expects($this->once())
            ->method('removeAdjustment')
            ->with($adjustment);

        $subject = $this->createMock(OrderInterface::class);
        $subject
            ->expects($this->once())
            ->method('hasShipments')
            ->willReturn(true);

        $subject
            ->expects($this->once())
            ->method('getShipments')
            ->willReturn(new ArrayCollection([$shipment]));

        $this->promotion->method('getCode')->willReturn('PROMO_CODE');

        $this->command->revert($subject, [], $this->promotion);
    }
}
