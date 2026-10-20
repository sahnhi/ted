<?php

namespace App\Tests\Unit\Checker;

use App\Checker\PromotionCouponEligibilityChecker;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Promotion;
use App\Doctrine\Entity\Sylius\PromotionCoupon;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionCouponEligibilityCheckerInterface;
use Sylius\Component\Promotion\Checker\Eligibility\PromotionEligibilityCheckerInterface;

class PromotionCouponEligibilityCheckerTest extends TestCase
{
    /** @var Order */
    private $order;
    /** @var PromotionCoupon */
    private $promotionCoupon;

    /** @var MockObject|PromotionEligibilityCheckerInterface */
    private $promotionEligibilityChecker;
    /** @var MockObject|PromotionCouponEligibilityCheckerInterface */
    private $couponEligibilityChecker;

    /** @var PromotionCouponEligibilityChecker */
    private $checker;

    public function setUp(): void
    {
        parent::setUp();

        $channel = new Channel();
        $this->order = new Order();
        $this->order->setChannel($channel);
        $this->promotionCoupon = new PromotionCoupon();
        $promotion = new Promotion();
        $promotion->addChannel($channel);
        $this->promotionCoupon->setPromotion($promotion);

        $this->promotionEligibilityChecker = $this->createMock(PromotionEligibilityCheckerInterface::class);
        $this->couponEligibilityChecker = $this->createMock(PromotionCouponEligibilityCheckerInterface::class);
    }

    private function setUpChecker()
    {
        $this->checker = new PromotionCouponEligibilityChecker(
            $this->promotionEligibilityChecker,
            $this->couponEligibilityChecker
        );
    }

    public function testIsEligible(): void
    {
        $this->promotionEligibilityChecker
            ->method('isEligible')
            ->willReturn(true);
        $this->couponEligibilityChecker
            ->method('isEligible')
            ->willReturn(true);
        $this->setUpChecker();

        $this->assertTrue($this->checker->isEligible($this->order, $this->promotionCoupon));
    }

    public function testIsEligibleDifferentChannel(): void
    {
        $this->order->setChannel(new Channel());
        $this->setUpChecker();

        $this->assertNotTrue($this->checker->isEligible($this->order, $this->promotionCoupon));
    }
}
