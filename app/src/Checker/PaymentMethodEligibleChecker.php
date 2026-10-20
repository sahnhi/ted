<?php

namespace App\Checker;

use App\Doctrine\Entity\PaymentMethodRuleInterface;
use App\Doctrine\Entity\Sylius\PaymentMethodInterface;

class PaymentMethodEligibleChecker
{
    public function isPaymentEligible(?int $amount, PaymentMethodInterface $paymentMethod): bool
    {
        if ($paymentMethod->getRules()->isEmpty()) {
            return true;
        }

        $paymentMethodEligible = false;
        $paymentMethodEligibleUnder = true;
        $paymentMethodEligibleOver = true;

        foreach ($paymentMethod->getRules() as $rule) {
            if (PaymentMethodRuleInterface::TYPE_INFERIOR === $rule->getType()) {
                $paymentMethodEligibleUnder = $amount < $rule->getAmount();
            }

            if (PaymentMethodRuleInterface::TYPE_SUPERIOR === $rule->getType()) {
                $paymentMethodEligibleOver = $amount > $rule->getAmount();
            }
        }

        if ($paymentMethodEligibleOver && $paymentMethodEligibleUnder) {
            $paymentMethodEligible = true;
        }

        return $paymentMethodEligible;
    }
}
