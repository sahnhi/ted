<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Controller\Webhooks;

use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use App\Doctrine\Repository\Sylius\PaymentRepositoryInterface;
use App\Event\CancelOrderEvent;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CancelledAction
{
    public const CANCELLED_LABEL = 'cancelled';

    private $paymentManager;
    private $paymentRepository;
    private $logger;
    private $eventDispatcher;

    public function __construct(
        ObjectManager $paymentManager,
        PaymentRepositoryInterface $paymentRepository,
        EventDispatcherInterface $eventDispatcher,
        Logger $logger)
    {
        $this->paymentManager = $paymentManager;
        $this->paymentRepository = $paymentRepository;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(Request $request): Response
    {
        $this->logger->info('CancelledAction FROM ' . $_SERVER['REMOTE_ADDR']);
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
        $details['younitedpay']['current_status'] = self::CANCELLED_LABEL;
        $details['younitedpay']['status_history'][date('Y-m-d_H:i:s')][self::CANCELLED_LABEL] = [
            'webhook' => true,
        ];
        $details['younitedpay']['cancel_source'] = PaymentMethodInterface::YOUNITED;

        $payment->setDetails($details);
        $payment->setState(self::CANCELLED_LABEL);

        $this->paymentManager->flush();
        $this->eventDispatcher->dispatch(new CancelOrderEvent($payment->getOrder()), CancelOrderEvent::NAME);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
