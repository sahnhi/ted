<?php

declare(strict_types=1);

namespace App\Tests\Unit\Promotion\Action;

use App\Doctrine\Repository\Sylius\PromotionRepositoryInterface;
use App\Promotion\Action\ApplyHighestPriorityPromotionPromotionActionCommand;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Promotion\Action\PromotionActionCommandInterface;
use Sylius\Component\Promotion\Action\PromotionApplicatorInterface;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionEligibilityCheckerInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;

class ApplyHighestPriorityPromotionPromotionActionCommandTest extends TestCase
{
    private PromotionRepositoryInterface $promotionRepository;
    private PromotionApplicatorInterface $promotionApplicator;
    private PromotionEligibilityCheckerInterface $promotionEligibilityChecker;
    private PromotionActionCommandInterface $command;

    protected function setUp(): void
    {
        $this->promotionRepository = $this->createMock(PromotionRepositoryInterface::class);
        $this->promotionApplicator = $this->createMock(PromotionApplicatorInterface::class);
        $this->promotionEligibilityChecker = $this->createMock(PromotionEligibilityCheckerInterface::class);

        $this->command = new ApplyHighestPriorityPromotionPromotionActionCommand(
            $this->promotionRepository,
            $this->promotionApplicator,
            $this->promotionEligibilityChecker
        );
    }

    public function testExecuteWithInvalidSubjectThrowsException(): void
    {
        $subject = $this->createMock(PromotionSubjectInterface::class);
        $promotion = $this->createMock(PromotionInterface::class);

        $this->expectException(UnexpectedTypeException::class);

        $this->command->execute($subject, ['promotions' => []], $promotion);
    }

    public function testRevertWithInvalidSubjectThrowsException(): void
    {
        $subject = $this->createMock(PromotionSubjectInterface::class);
        $promotion = $this->createMock(PromotionInterface::class);

        $this->expectException(UnexpectedTypeException::class);

        $this->command->revert($subject, ['promotions' => []], $promotion);
    }

    public function testExecuteAppliesHighestPriorityPromotion(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $highestPriorityPromotion = $this->createMock(PromotionInterface::class);
        $lowerPriorityPromotion = $this->createMock(PromotionInterface::class);

        $order
            ->method('getChannel')
            ->willReturn($this->createMock(Channel::class));

        $this->promotionRepository
            ->method('findActiveByCodeAndChannel')
            ->with(['promotion_1', 'promotion_2'], $order->getChannel(), false)
            ->willReturn([$highestPriorityPromotion, $lowerPriorityPromotion]);

        $this->promotionEligibilityChecker
            ->expects($this->once())
            ->method('isEligible')
            ->with($order, $highestPriorityPromotion)
            ->willReturn(true);

        $this->promotionApplicator
            ->expects($this->once())
            ->method('apply')
            ->with($order, $highestPriorityPromotion);

        $order
            ->expects($this->once())
            ->method('addPromotion')
            ->with($highestPriorityPromotion);

        $parentPromotion = $this->createMock(PromotionInterface::class);
        $result = $this->command->execute($order, ['promotions' => ['promotion_1', 'promotion_2']], $parentPromotion);

        $this->assertTrue($result);
    }

    public function testExecuteAppliesHighestEligiblePriorityPromotion(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $highestPriorityUnEligiblePromotion = $this->createMock(PromotionInterface::class);
        $lowerPriorityEligiblePromotion = $this->createMock(PromotionInterface::class);
        $randomThirdPromotion = $this->createMock(PromotionInterface::class);

        $order
            ->method('getChannel')
            ->willReturn($this->createMock(Channel::class));

        $this->promotionRepository
            ->method('findActiveByCodeAndChannel')
            ->with(['promotion_1', 'promotion_2', 'promotion_3'], $order->getChannel(), false)
            ->willReturn([$highestPriorityUnEligiblePromotion, $lowerPriorityEligiblePromotion, $randomThirdPromotion]);

        $this->promotionEligibilityChecker
            ->expects($this->exactly(2))
            ->method('isEligible')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->promotionApplicator
            ->expects($this->once())
            ->method('apply')
            ->with($order, $lowerPriorityEligiblePromotion);

        $order
            ->expects($this->once())
            ->method('addPromotion')
            ->with($lowerPriorityEligiblePromotion);

        $parentPromotion = $this->createMock(PromotionInterface::class);
        $result = $this->command->execute($order, ['promotions' => ['promotion_1', 'promotion_2', 'promotion_3']], $parentPromotion);

        $this->assertTrue($result);
    }

    public function testExecuteWithoutEligiblePromotionReturnsFalse(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $highestPriorityPromotion = $this->createMock(PromotionInterface::class);
        $lowerPriorityPromotion = $this->createMock(PromotionInterface::class);

        $order
            ->method('getChannel')
            ->willReturn($this->createMock(Channel::class));

        $this->promotionRepository
            ->method('findActiveByCodeAndChannel')
            ->with(['promotion_1', 'promotion_2'], $order->getChannel(), false)
            ->willReturn([$highestPriorityPromotion, $lowerPriorityPromotion]);

        $this->promotionEligibilityChecker
            ->expects($this->exactly(2))
            ->method('isEligible')
            ->willReturn(false);

        $this->promotionApplicator
            ->expects($this->never())
            ->method('apply');

        $parentPromotion = $this->createMock(PromotionInterface::class);
        $result = $this->command->execute($order, ['promotions' => ['promotion_1', 'promotion_2']], $parentPromotion);

        $this->assertFalse($result);
    }

    public function testRevertRemovesAppliedPromotions(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $highestPriorityPromotion = $this->createMock(PromotionInterface::class);

        $order
            ->method('getChannel')
            ->willReturn($this->createMock(Channel::class));

        $order
            ->method('hasPromotion')
            ->with($highestPriorityPromotion)
            ->willReturn(true);

        $this->promotionRepository
            ->method('findActiveByCodeAndChannel')
            ->with(['promotion_1'], $order->getChannel(), false)
            ->willReturn([$highestPriorityPromotion]);

        $this->promotionApplicator
            ->expects($this->once())
            ->method('revert')
            ->with($order, $highestPriorityPromotion);

        $order
            ->expects($this->once())
            ->method('removePromotion')
            ->with($highestPriorityPromotion);

        $parentPromotion = $this->createMock(PromotionInterface::class);
        $this->command->revert($order, ['promotions' => ['promotion_1']], $parentPromotion);
    }

    public function testRevertDoesNothingWhenNoPromotionToRemove(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $highestPriorityPromotion = $this->createMock(PromotionInterface::class);

        $order
            ->method('getChannel')
            ->willReturn($this->createMock(Channel::class));

        $order
            ->method('hasPromotion')
            ->with($highestPriorityPromotion)
            ->willReturn(false);

        $this->promotionRepository
            ->method('findActiveByCodeAndChannel')
            ->with(['promotion_1'], $order->getChannel(), false)
            ->willReturn([$highestPriorityPromotion]);

        $this->promotionApplicator
            ->expects($this->never())
            ->method('revert');

        $order
            ->expects($this->never())
            ->method('removePromotion');

        $parentPromotion = $this->createMock(PromotionInterface::class);
        $this->command->revert($order, ['promotions' => ['promotion_1']], $parentPromotion);
    }
}
