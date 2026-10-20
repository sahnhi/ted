<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\StateMachine;

use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayApi;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayContract;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class CancelContractProcessor
{
    public const YOUNITEDPAY_PAYMENT_METHOD_NAME = 'younitedpay';
    public const CANCELLED_LABEL = 'cancelled';

    private $flashBag;

    public function __construct(
        FlashBagInterface $flashBag,
        private readonly Logger $logger
    ) {
        $this->flashBag = $flashBag;
    }

    public function cancel(OrderInterface $order): void
    {
        // Get last payment from $order Object
        $payment = $order->getLastPayment();
        /** @var PaymentInterface $payment */
        if (null === $payment) {
            return;
        }

        $gatewayConfig = $payment->getMethod()->getGatewayConfig();

        if (self::YOUNITEDPAY_PAYMENT_METHOD_NAME === $gatewayConfig->getGatewayName()) {
            $details = $payment->getDetails();

            // Cancel Contract
            $response = $this->cancelContract($payment);

            if (204 === $response['statusCode']) {
                $details['younitedpay']['current_status'] = self::CANCELLED_LABEL;
                $order->setPaymentState(self::CANCELLED_LABEL);
                $this->flashBag->add('success', 'Le contrat YounitedPay a été annulé');
            } else {
                $this->flashBag->add('error', 'Le contrat YounitedPay n\'a pas pu être annulé');
            }
            $details['younitedpay']['status_history'][date('Y-m-d_H:i:s')][self::CANCELLED_LABEL] = $response;

            // Update details
            $payment->setDetails($details);
        }
    }

    private function cancelContract(PaymentInterface $payment)
    {
        if (!isset($payment->getDetails()['younitedpay']) or !isset($payment->getDetails()['younitedpay']['contractReference'])) {
            return [
                'statusCode' => null,
                'errors' => [
                    'Could not find contractReference',
                    'API call not launched',
                ],
            ];
        }

        $younitedpayApi = $this->getYounitedpayApi($payment->getMethod()->getGatewayConfig()->getConfig());
        $younitedpayContract = new YounitedpayContract($younitedpayApi, $this->logger);

        return $younitedpayContract->cancelContract($payment->getDetails()['younitedpay']['contractReference']);
    }

    private function getYounitedpayApi(array $config)
    {
        $config = new \ArrayObject($config);
        $younitedpayApi = new YounitedpayApi();
        $younitedpayApi->initialize($config);

        return $younitedpayApi;
    }
}
