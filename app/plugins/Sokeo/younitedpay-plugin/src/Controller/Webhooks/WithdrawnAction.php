<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Controller\Webhooks;

use App\Doctrine\Repository\Sylius\PaymentRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WithdrawnAction
{
    public const WITHDRAWN_LABEL = 'withdrawn';
    public const FAILED_LABEL = 'failed';

    private $paymentManager;
    private $paymentRepository;
    private $logger;

    public function __construct(
        ObjectManager $paymentManager,
        PaymentRepositoryInterface $paymentRepository,
        Logger $logger)
    {
        $this->paymentManager = $paymentManager;
        $this->paymentRepository = $paymentRepository;
        $this->logger = $logger;
    }

    public function __invoke(Request $request): Response
    {
        $this->logger->info('WithdrawnAction FROM ' . $_SERVER['REMOTE_ADDR']);
        $paymentId = $request->attributes->get('paymentId');
        if (!$paymentId) {
            return new JsonResponse(['error' => 'Missing payment id'], Response::HTTP_NOT_FOUND);
        }

        $payment = $this->paymentRepository->find($paymentId);
        if (!$payment) {
            return new JsonResponse(['error' => 'Unknown payment'], Response::HTTP_NOT_FOUND);
        }

        // Update details
        $details = $payment->getDetails();
        $details['younitedpay']['current_status'] = self::WITHDRAWN_LABEL;
        $details['younitedpay']['status_history'][date('Y-m-d_H:i:s')][self::WITHDRAWN_LABEL] = [
            'webhook' => true,
        ];
        $payment->setDetails($details);
        $payment->setState(self::FAILED_LABEL);

        $this->paymentManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
