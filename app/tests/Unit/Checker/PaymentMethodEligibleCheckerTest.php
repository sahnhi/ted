<?php

namespace App\Tests\Unit\Checker;

use App\Checker\PaymentMethodEligibleChecker;
use App\Doctrine\Entity\PaymentMethodRule;
use App\Doctrine\Entity\PaymentMethodRuleInterface;
use App\Doctrine\Entity\Sylius\PaymentMethod;
use PHPUnit\Framework\TestCase;

class PaymentMethodEligibleCheckerTest extends TestCase
{
    /** @var PaymentMethodEligibleChecker */
    private $checker;

    public function setUp(): void
    {
        parent::setUp();
        $this->checker = new PaymentMethodEligibleChecker();
    }

    public function testIsPaymentEligible(): void
    {
        $this->assertTrue($this->checker->isPaymentEligible(0, new PaymentMethod()));
    }

    public function testIsPaymentEligibleOver(): void
    {
        $rule = new PaymentMethodRule();
        $rule->setAmount(50);
        $rule->setType(PaymentMethodRuleInterface::TYPE_SUPERIOR);
        $paymentMethod = new PaymentMethod();
        $paymentMethod->addRule($rule);

        $this->assertTrue($this->checker->isPaymentEligible(100, $paymentMethod));
    }

    public function testIsPaymentEligibleUnder(): void
    {
        $rule = new PaymentMethodRule();
        $rule->setAmount(50);
        $rule->setType(PaymentMethodRuleInterface::TYPE_INFERIOR);
        $paymentMethod = new PaymentMethod();
        $paymentMethod->addRule($rule);

        $this->assertTrue($this->checker->isPaymentEligible(20, $paymentMethod));
    }

    public function testIsPaymentEligibleNotEligible(): void
    {
        $rule = new PaymentMethodRule();
        $rule->setAmount(50);
        $rule->setType(PaymentMethodRuleInterface::TYPE_SUPERIOR);
        $paymentMethod = new PaymentMethod();
        $paymentMethod->addRule($rule);

        $this->assertNotTrue($this->checker->isPaymentEligible(20, $paymentMethod));
    }
}
