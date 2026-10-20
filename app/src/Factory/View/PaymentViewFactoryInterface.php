<?php

namespace App\Factory\View;

use App\View\PaymentView;
use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentViewFactoryInterface
{
    public function create(PaymentInterface $payment, string $locale): PaymentView;
}
