<?php

namespace App\Payum\Dalenys\PaymentProcessing;

use App\Payum\Dalenys\Dalenys3TimesGatewayFactory;
use Payum\Core\Payum;
use Payum\Core\Request\Refund;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class RefundPaymentProcessor
{
    public function __construct(
        private readonly Payum $payum,
        private readonly FlashBagInterface $flashBag
    ) {
    }

    public function process(PaymentInterface $payment, int $refundAmount): void
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        if (Dalenys3TimesGatewayFactory::FACTORY_NAME !== $paymentMethod->getGatewayConfig()->getFactoryName()) {
            return;
        }

        $model = $payment->getDetails();

        if (0 === $refundAmount) {
            throw new UpdateHandlingException(sprintf('Refund impossible : the amount is 0.'));
        }

        $extraData = !empty($payment->getDetails()['EXTRADATA']) ? json_decode((string) $payment->getDetails()['EXTRADATA'], true) : [];

        if (true === empty($extraData) || false === isset($extraData['refund_token'])) {
            $this->flashBag->add('info', 'sylius.payment.refund_made_only_locally');

            return;
        }

        $refundHash = $extraData['refund_token'];

        if (null === $refundToken = $this->payum->getTokenStorage()->find($refundHash)) {
            throw new UpdateHandlingException(sprintf('A token with hash "%s" could not be found.', $refundHash));
        }

        $model['refund'] = [
            'currency' => $payment->getCurrencyCode(),
            'value' => $refundAmount,
        ];

        $gateway = $this->payum->getGateway($refundToken->getGatewayName());
        $gateway->execute($model = new Refund($model));

        $payment->setDetails($model->getmodel()->getArrayCopy());

        $this->flashBag->add('success', 'sylius.payment.refund_successful');
    }
}
