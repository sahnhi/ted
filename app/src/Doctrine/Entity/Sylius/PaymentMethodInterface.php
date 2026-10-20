<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\PaymentMethodRuleInterface;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\PaymentMethodInterface as BasePaymentMethodInterface;

interface PaymentMethodInterface extends BasePaymentMethodInterface
{
    public const ADYEN_CB = 'adyen_cb';
    public const ADYEN_PAYPAL = 'adyen_paypal';
    public const BE2BILL_NTIMES = 'be2bill_ntimes';
    public const BE2BILL_NTIMES_DELAYED = 'be2bill_ntimes_delayed';
    public const YOUNITED = 'younited';
    public const YOUNITED_PAY = 'younitedpay';

    public const YOUNITED_METHODS = [
        self::YOUNITED,
        self::YOUNITED_PAY,
    ];

    public const PAYMENT_MULTIPLE_TIMES = [
        self::BE2BILL_NTIMES,
        self::BE2BILL_NTIMES_DELAYED,
    ];

    /**
     * @return Collection|PaymentMethodRuleInterface[]
     */
    public function getRules(): Collection;

    public function addRule(PaymentMethodRuleInterface $rule): void;

    public function removeRule(PaymentMethodRuleInterface $rule): void;
}
