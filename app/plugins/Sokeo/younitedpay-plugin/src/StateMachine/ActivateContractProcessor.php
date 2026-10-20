<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\StateMachine;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Services\Channel\ChannelCode;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayApi;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayContract;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class ActivateContractProcessor
{
    private const YOUNITEDPAY_PAYMENT_METHOD_NAME = 'younitedpay';
    private const ACTIVATED_LABEL = 'activated';

    private FlashBagInterface $flashBag;
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        FlashBagInterface $flashBag,
        OrderRepositoryInterface $orderRepository,
        private readonly Logger $logger
    ) {
        $this->flashBag = $flashBag;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Order $order
     */
    public function activate(OrderInterface $order): void
    {
        if (ChannelCode::CHANNEL_BIS === $order->getChannel()?->getCode()) {
            $initialOrderNumber = $order->getNumber();

            /** @var Order|null $order */
            $order = $this->orderRepository->getOrderParent($order);

            if (null === $order) {
                $this->flashBag->add('error', sprintf('Impossible de trouver la commande parente avec le numéro "%s"', $initialOrderNumber));

                return;
            }
        }

        // Get last payment from $order Object
        $payment = $order->getLastPayment(BasePaymentInterface::STATE_COMPLETED);
        /** @var PaymentInterface $payment */
        if (null === $payment) {
            return;
        }

        $paymentMethod = $payment->getMethod();

        if (!$paymentMethod instanceof PaymentMethodInterface) {
            return;
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (self::YOUNITEDPAY_PAYMENT_METHOD_NAME === $gatewayConfig->getGatewayName()) {
            $details = $payment->getDetails();

            // Activate Contract
            $response = $this->activateContract($payment);

            if (204 === $response['statusCode']) {
                $details['younitedpay']['current_status'] = self::ACTIVATED_LABEL;
                $this->flashBag->add('success', 'Le contrat YounitedPay a été activé');
            } else {
                $this->flashBag->add('error', 'Le contrat YounitedPay n\'a pas pu être activé');
            }
            $details['younitedpay']['status_history'][date('Y-m-d_H:i:s')][self::ACTIVATED_LABEL] = $response;

            // Update details
            $payment->setDetails($details);
        }
    }

    /**
     * @return array
     */
    private function activateContract(PaymentInterface $payment)
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

        return $younitedpayContract->activateContract($payment->getDetails()['younitedpay']['contractReference']);
    }

    private function getYounitedpayApi(array $config): YounitedpayApi
    {
        $config = new \ArrayObject($config);
        $younitedpayApi = new YounitedpayApi();
        $younitedpayApi->initialize($config);

        return $younitedpayApi;
    }
}
