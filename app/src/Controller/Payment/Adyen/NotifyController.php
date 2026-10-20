<?php

namespace App\Controller\Payment\Adyen;

use App\Services\Sentry\Reporting;
use Payum\Core\Payum;
use Payum\Core\Request\Notify;
use Payum\Core\Security\TokenInterface;
use Sentry;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class NotifyController
{
    public function __construct(
        private readonly Payum $payum,
        private readonly RepositoryInterface $paymentRepository,
        private readonly Reporting $reporting,
    ) {
    }

    public function doAction(Request $request): Response
    {
        $notificationItems = $request->request->get('notificationItems', []);

        if (0 === count($notificationItems) || !isset($notificationItems[0]['NotificationRequestItem']['merchantReference'])) {
            $this->reportError('Merchant Reference needed');

            // this exact body and code is required by Adyen
            return new Response('[accepted]', Response::HTTP_OK);
        }

        $merchantReference = $notificationItems[0]['NotificationRequestItem']['merchantReference'];

        $matches = [];
        preg_match('/^(?<orderId>\w*)-(?<paymentId>\d+)$/', (string) $merchantReference, $matches);

        $paymentId = $matches['paymentId'] ?? null;

        if (null === $paymentId) {
            $this->reportError(sprintf('No payment id found for merchantReference "%s"', $merchantReference));

            // this exact body and code is required by Adyen
            return new Response('[accepted]', Response::HTTP_OK);
        }

        $payment = $this->paymentRepository->find((int) $paymentId);
        if (!$payment instanceof PaymentInterface) {
            $this->reportError(sprintf('No payment found for payment id %d', $paymentId));

            return new Response('[accepted]', Response::HTTP_OK);
        }

        $paymentExtraData = json_decode((string) $payment->getDetails()['extraData'], true);
        if (empty($paymentExtraData['notifyToken'])) {
            $this->reportError(sprintf('No notifyToken found in details for payment with id %d', $paymentId));

            return new Response('[accepted]', Response::HTTP_OK);
        }

        /** @var TokenInterface|null $token */
        $token = $this->payum->getTokenStorage()->find($paymentExtraData['notifyToken']);
        if (null === $token) {
            $this->reportError(sprintf('No paymentToken found for notifyToken "%s"', $paymentExtraData['notifyToken']));

            return new Response('[accepted]', Response::HTTP_OK);
        }

        $this->payum
            ->getGateway($token->getGatewayName())
            ->execute(new Notify($token));

        // this exact body and code is required by Adyen
        return new Response('[accepted]', Response::HTTP_OK);
    }

    private function reportError(string $message): void
    {
        $this->reporting->reportIssue($message, Sentry\Severity::error());
    }
}
