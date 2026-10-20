<?php

namespace App\Payum\Adyen\PaymentProcessing;

use App\Payum\Adyen\AdyenGatewayFactory;
use Doctrine\Persistence\ObjectManager;
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
        private readonly FlashBagInterface $flashBag,
        private readonly ObjectManager $paymentManager
    ) {
    }

    public function process(PaymentInterface $payment, int $refundAmount): void
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        if (AdyenGatewayFactory::FACTORY_NAME !== $paymentMethod->getGatewayConfig()->getFactoryName()) {
            return;
        }

        $model = $payment->getDetails();

        $model['refundedAmount'] = [
            'currency' => $payment->getCurrencyCode(),
            'value' => !empty($model['refundedAmount']) ? $model['refundedAmount']['value'] + $refundAmount : $refundAmount,
        ];

        if ($model['refundedAmount']['value'] > $payment->getAmount()) {
            throw new UpdateHandlingException('Refunded amount oversized : refund cancelled.');
        }

        if (0 === $refundAmount) {
            throw new UpdateHandlingException(sprintf('Refund impossible : the amount is 0.'));
        }

        $extraData = !empty($payment->getDetails()['extraData']) ? json_decode((string) $payment->getDetails()['extraData'], true) : [];

        if (true === empty($extraData) || false === isset($extraData['refundToken'])) {
            $this->flashBag->add('info', 'sylius.payment.refund_made_only_locally');

            return;
        }

        $refundHash = $extraData['refundToken'];

        if (null === $refundToken = $this->payum->getTokenStorage()->find($refundHash)) {
            throw new UpdateHandlingException(sprintf('A token with hash "%s" could not be found.', $refundHash));
        }

        $model['refund'] = [
            'currency' => $model['amount']['currency'],
            'value' => $refundAmount,
        ];

        $gateway = $this->payum->getGateway($refundToken->getGatewayName());
        $gateway->execute($model = new Refund($model));

        $payment->setDetails($model->getModel()->getArrayCopy());

        $this->paymentManager->persist($payment);

        $this->flashBag->add('success', 'sylius.payment.refund_successful');
    }
}
