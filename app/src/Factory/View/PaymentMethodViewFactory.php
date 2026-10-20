<?php

namespace App\Factory\View;

use App\View\PaymentMethodView;
use Sylius\Component\Core\Model\PaymentMethodInterface;

final class PaymentMethodViewFactory implements PaymentMethodViewFactoryInterface
{
    public function create(PaymentMethodInterface $paymentMethod, string $locale): PaymentMethodView
    {
        $paymentMethodView = new PaymentMethodView();
        $paymentMethodView->code = $paymentMethod->getCode();
        $paymentMethodView->name = $paymentMethod->getTranslation($locale)->getName();
        $paymentMethodView->description = $paymentMethod->getTranslation($locale)->getDescription();

        return $paymentMethodView;
    }
}
