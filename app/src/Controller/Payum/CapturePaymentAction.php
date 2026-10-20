<?php

namespace App\Controller\Payum;

use App\Model\Payum\Payment as PayumPayment;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class CapturePaymentAction extends GatewayAwareAction
{
    public function __construct(
        private readonly PaymentDescriptionProviderInterface $paymentDescriptionProvider,
        private readonly LoggerInterface $routingLogger
    ) {
    }

    /**
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        if ($order) {
            $this->routingLogger->info(sprintf('order number "%s" is captured at %s', $order->getNumber(), (new \DateTime())->format(\DateTimeInterface::ATOM)));
        }

        $this->gateway->execute($status = new GetStatus($payment));
        if ($status->isNew()) {
            try {
                $this->gateway->execute($convert = new Convert($payment, 'array', $request->getToken()));
                $payment->setDetails($convert->getResult());
            } catch (RequestNotSupportedException) {
                $totalAmount = $order->getTotal();
                $payumPayment = new PayumPayment();
                $payumPayment->setNumber($order->getNumber());
                $payumPayment->setTotalAmount($totalAmount);
                $payumPayment->setCurrencyCode($order->getCurrencyCode());
                $payumPayment->setClientEmail($order->getCustomer()->getEmail());
                $payumPayment->setClientId($order->getCustomer()->getId());
                $payumPayment->setDescription($this->paymentDescriptionProvider->getPaymentDescription($payment));
                $payumPayment->setDetails($payment->getDetails());

                /* only the new line is custom from the original file */
                $payumPayment->setOrder($order);

                $this->gateway->execute($convert = new Convert($payumPayment, 'array', $request->getToken()));
                $payment->setDetails($convert->getResult());
            }
        }

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        try {
            $request->setModel($details);
            $this->gateway->execute($request);
        } finally {
            $payment->setDetails((array) $details);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture
            && $request->getModel() instanceof SyliusPaymentInterface;
    }
}
