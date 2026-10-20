<?php

declare(strict_types=1);

namespace App\Handler\Shared;

use Payum\Core\Payum;
use SM\Factory\Factory;
use SM\SMException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class OrderResetter
{
    public function __construct(
        private readonly Factory $stateMachineFactory,
        private readonly Payum $payum,
    ) {
    }

    /**
     * @throws SMException
     */
    public function cancelPaymentAndResetOrder(OrderInterface $order): void
    {
        $ongoingPayment = $order->getLastPayment('cart');
        if ($ongoingPayment) {
            try {
                $payumToken = json_decode((string) $ongoingPayment->getDetails()['extraData'], true)['captureToken'];
                $tokenStorage = $this->payum->getTokenStorage();
                $tokenStorage->delete($tokenStorage->find($payumToken));
            } finally {
                $sm = $this->stateMachineFactory->get($ongoingPayment, PaymentTransitions::GRAPH);
                $sm->apply(PaymentTransitions::TRANSITION_CANCEL);
            }
        }
    }
}
