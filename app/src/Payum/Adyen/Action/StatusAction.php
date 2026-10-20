<?php

namespace App\Payum\Adyen\Action;

use App\Bridge\AdyenBridgeInterface;
use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistry;
use App\Services\Sentry\Reporting;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetStatusInterface;
use Sentry;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class StatusAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    protected AdyenBridgeInterface $api;

    public function __construct(
        private readonly Reporting $reporting,
        private readonly EmailConfigurationRegistry $emailConfigurationRegistry,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function setApi($api): void
    {
        if (!$api instanceof AdyenBridgeInterface) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    public function execute($request): void
    {
        /** @var GetStatusInterface $request */
        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $details = $payment->getDetails();

        if (!isset($details['authResult'])) {
            $request->markNew();

            return;
        }

        $payment->setDetails($details);

        switch ($details['authResult']) {
            case null:
                $request->markNew();
                break;
            case AdyenBridgeInterface::EVENT_CODE_AUTHORISED:
            case AdyenBridgeInterface::EVENT_CODE_AUTHORISATION:
            case AdyenBridgeInterface::EVENT_CODE_CAPTURE:
                if (isset($details['amount']['value'])) {
                    // The amount Adyen tells us the client paid for during the transaction.
                    $adyenPaymentAmount = $details['amount']['value'];

                    // If the client paid for a different amount than the one we told them to pay,
                    // we consider the transaction as failed.
                    if ($adyenPaymentAmount !== $payment->getAmount()) {
                        // By marking it as failed, the UpdatePaymentStateExtension Payum extension
                        // will change state of the payment to 'failed'.
                        $request->markFailed();

                        $this->reporting->reportIssue(
                            'Adyen payment error : amount mismatch',
                            Sentry\Severity::error(),
                            extras: [
                                'adyen_amount' => $adyenPaymentAmount,
                                'payment_amount' => $payment->getAmount(),
                                'payment_id' => $payment->getId(),
                            ]
                        );

                        try {
                            $order = $payment->getOrder();
                            if (null !== $order && null !== ($customer = $order->getCustomer())) {
                                $message = (new Email())
                                    ->subject('Erreur de paiement Adyen - Montants invalides')
                                    ->from(new Address('support@tediber.com', 'TEDIBER Sylius'))
                                    ->to(...$this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::SAV)->getPrincipals())
                                    ->text(sprintf("Le client %s n'a été prélevé que de %01.2f€ alors qu'il aurait du payer %01.2f€ pour la commande d'id %d",
                                        $customer->getEmail(),
                                        $adyenPaymentAmount / 100,
                                        $payment->getAmount() / 100,
                                        $order->getId(),
                                    ));

                                $this->mailer->send($message);
                            }
                        } catch (TransportExceptionInterface|EmailConfigurationNotFoundException) {
                        }

                        return;
                    }
                }

                $request->markCaptured();
                break;
            case AdyenBridgeInterface::EVENT_CODE_PENDING:
                $request->markPending();
                break;
            case AdyenBridgeInterface::EVENT_CODE_CANCELLED:
            case AdyenBridgeInterface::EVENT_CODE_CANCELLATION:
            case AdyenBridgeInterface::EVENT_CODE_CANCEL_OR_REFUND:
                $request->markCanceled();
                break;
            case AdyenBridgeInterface::EVENT_CODE_REFUSED:
            case AdyenBridgeInterface::EVENT_CODE_ERROR:
                $request->markFailed();
                break;
            case AdyenBridgeInterface::EVENT_CODE_NOTIFICATION_OF_CHARGEBACK:
            case AdyenBridgeInterface::EVENT_CODE_CHARGEBACK:
            case AdyenBridgeInterface::EVENT_CODE_CHARGEBACK_REVERSED:
            case AdyenBridgeInterface::EVENT_CODE_REFUND_FAILED:
            case AdyenBridgeInterface::EVENT_CODE_CAPTURE_FAILED:
                $request->markSuspended();
                break;
            case AdyenBridgeInterface::EVENT_CODE_EXPIRE:
                $request->markExpired();
                break;
            case AdyenBridgeInterface::EVENT_CODE_REFUND:
            case AdyenBridgeInterface::EVENT_CODE_REFUNDED_REVERSED:
                $request->markRefunded();
                break;
            default:
                $request->markUnknown();
                break;
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface
            && $request->getModel() instanceof PaymentInterface
        ;
    }
}
