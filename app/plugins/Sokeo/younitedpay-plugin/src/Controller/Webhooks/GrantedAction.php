<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Controller\Webhooks;

use App\Business\Command\CompleteOrder;
use App\Doctrine\Repository\Sylius\PaymentRepositoryInterface;
use App\Services\Erp\ForwardToErp;
use Doctrine\Persistence\ObjectManager;
use League\Tactician\CommandBus;
use SM\Factory\Factory;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayApi;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayContract;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GrantedAction
{
    public const GRANTED_LABEL = 'granted';
    public const CONFIRMED_LABEL = 'confirmed';
    public const COMPLETED_LABEL = 'completed';

    private $paymentManager;
    private $paymentRepository;
    private $logger;
    private $smFactory;

    public function __construct(
        ObjectManager $paymentManager,
        PaymentRepositoryInterface $paymentRepository,
        Logger $logger,
        Factory $smFactory,
        private readonly CommandBus $commandBus,
        private readonly ForwardToErp $forwardToErpService,
    ) {
        $this->paymentManager = $paymentManager;
        $this->paymentRepository = $paymentRepository;
        $this->logger = $logger;
        $this->smFactory = $smFactory;
    }

    public function __invoke(Request $request): Response
    {
        $this->logger->info('[YOUNITED] GrantedAction FROM ' . $_SERVER['REMOTE_ADDR']);

        $paymentId = $request->attributes->get('paymentId');
        if (!$paymentId) {
            return new JsonResponse(['error' => 'Missing payment id'], Response::HTTP_NOT_FOUND);
        }

        $payment = $this->paymentRepository->find($paymentId);
        if (!$payment) {
            return new JsonResponse(['error' => 'Unknown payment'], Response::HTTP_NOT_FOUND);
        }

        $stateMachine = $this->smFactory->get($payment, PaymentTransitions::GRAPH);

        if ($stateMachine->can(PaymentTransitions::TRANSITION_CREATE)) {
            $stateMachine->apply(PaymentTransitions::TRANSITION_CREATE);
        }

        if ($stateMachine->can(PaymentTransitions::TRANSITION_COMPLETE)) {
            $stateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);
        }

        // Update details
        $details = $payment->getDetails();
        if (self::CONFIRMED_LABEL !== $details['younitedpay']['current_status']) {
            $details['younitedpay']['current_status'] = self::GRANTED_LABEL;
        }
        $details['younitedpay']['status_history'][date('Y-m-d_H:i:s')][self::GRANTED_LABEL] = [
            'webhook' => true,
        ];

        // Confirm Contract
        $response = $this->confirmContract($payment);

        if (204 === $response['statusCode']) {
            $details['younitedpay']['current_status'] = self::CONFIRMED_LABEL;
        }
        $details['younitedpay']['status_history'][date('Y-m-d_H:i:s')][self::CONFIRMED_LABEL] = $response;

        $payment->setDetails($details);

        $this->paymentManager->flush();

        if (PaymentInterface::STATE_COMPLETED === $payment->getState()) {
            $order = $payment->getOrder();
            $checkoutCompletedAt = new \DateTime();
            $completeOrder = new CompleteOrder(
                $order->getTokenValue(),
                $order->getCustomer()->getEmail(),
                $checkoutCompletedAt,
                $request
            );
            $this->commandBus->handle($completeOrder);

            $this->forwardToErpService->postComplete($order);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    private function confirmContract(PaymentInterface $payment)
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

        return $younitedpayContract->confirmContract($payment->getDetails()['younitedpay']['contractReference']);
    }

    private function getYounitedpayApi(array $config)
    {
        $config = new \ArrayObject($config);
        $younitedpayApi = new YounitedpayApi();
        $younitedpayApi->initialize($config);

        return $younitedpayApi;
    }
}
