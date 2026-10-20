<?php

namespace App\Factory\View;

use App\View\PaymentView;
use Sylius\Component\Core\Model\PaymentInterface;

class PaymentViewFactory implements PaymentViewFactoryInterface
{
    public function __construct(
        private readonly PaymentMethodViewFactoryInterface $paymentMethodViewFactory,
        private readonly PriceViewFactoryInterface $priceViewFactory
    ) {
    }

    public function create(PaymentInterface $payment, string $locale): PaymentView
    {
        $paymentView = new PaymentView();
        $paymentView->state = $payment->getState();
        $paymentView->method = $this->paymentMethodViewFactory->create($payment->getMethod(), $locale);
        $paymentView->price = $this->priceViewFactory->create($payment->getAmount(), $payment->getCurrencyCode());

        return $paymentView;
    }
}
