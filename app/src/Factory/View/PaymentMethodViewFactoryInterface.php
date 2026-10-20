<?php

namespace App\Factory\View;

use App\View\PaymentMethodView;
use Sylius\Component\Core\Model\PaymentMethodInterface;

interface PaymentMethodViewFactoryInterface
{
    public function create(PaymentMethodInterface $paymentMethod, string $locale): PaymentMethodView;
}
