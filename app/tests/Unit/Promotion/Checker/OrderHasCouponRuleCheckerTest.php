<?php

declare(strict_types=1);

namespace App\Tests\Unit\Promotion\Checker;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PromotionCoupon;
use App\Promotion\Checker\Rule\OrderHasCouponRuleChecker;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

class OrderHasCouponRuleCheckerTest extends TestCase
{
    private OrderHasCouponRuleChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new OrderHasCouponRuleChecker();
    }

    public function testItReturnsFalseIfTheConfigurationDoesNotHaveTheRequiredField(): void
    {
        self::assertFalse($this->checker->isEligible(new Order(), []));
    }

    public function testItThrowsAnUnsupportedTypeExceptionIfTheSubjectIsNotAnOrder(): void
    {
        $configuration = ['coupon' => 'COUPON_CODE'];

        $this->expectException(UnsupportedTypeException::class);

        $this->checker->isEligible($this->createMock(PromotionSubjectInterface::class), $configuration);
    }

    public function testItReturnsFalseIfTheOrderDoesNotHaveTheRequiredCoupon(): void
    {
        $coupon = new PromotionCoupon();
        $coupon->setCode('NOT_THIS_ONE');

        $order = new Order();
        $order->setPromotionCoupon($coupon);

        self::assertFalse($this->checker->isEligible($order, ['coupon' => 'THIS_ONE']));
    }

    public function testItReturnsTrueIfTheOrderDoesHaveTheRequiredCoupon(): void
    {
        $coupon = new PromotionCoupon();
        $coupon->setCode('THIS_ONE');

        $order = new Order();
        $order->setPromotionCoupon($coupon);

        self::assertTrue($this->checker->isEligible($order, ['coupon' => 'THIS_ONE']));
    }
}
