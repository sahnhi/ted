<?php

namespace App\Controller\Payment;

use App\Payum\Dalenys\Api;
use Payum\Core\Payum;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DalenysCallbackAction
{
    public function __construct(
        private readonly Payum $payum,
        private readonly RepositoryInterface $paymentRepository
    ) {
    }

    public function __invoke(Request $request, string $gatewayName, string $action): Response
    {
        $execCode = $request->request->get('EXECCODE');
        $extraData = $request->request->get('EXTRADATA');

        if ('capture' === $action) {
            $extraData = $request->query->get('EXTRADATA');
        }

        $schedule = (int) current(explode('-', $request->request->get('SCHEDULE')));

        if ($schedule > 1 || Api::OPERATION_REFUND === $request->request->get('OPERATIONTYPE')) {
            $orderNumber = $request->request->get('ORDERID');

            if ($payment = $this->paymentRepository->findByOrderNumberAndCompletedState($orderNumber)) {
                $details = $payment->getDetails();
                $extraData = $details['EXTRADATA'];
            }
        }

        if (!$extraData) {
            throw new BadRequestHttpException('Dalenys request is invalid');
        }

        $gateway = $this->payum->getGateway($gatewayName);
        $extraData = json_decode((string) $extraData, true);

        switch ($action) {
            case 'capture':
                if (!isset($extraData['capture_token'])) {
                    throw new BadRequestHttpException('Capture token is not set');
                }

                $gateway->execute($getToken = new GetToken($extraData['capture_token']));
                $gateway->execute(new Capture($getToken->getToken()));

                // Je ne supprime pas car à terme il faudra changer la logique par rapport à Payum et réactiver l'invalidation des tokens
                // $this->payum->getHttpRequestVerifier()->invalidate($getToken->getToken());

                return RedirectResponse::create($getToken->getToken()->getAfterUrl());
            case 'notify':
                if (!isset($extraData['notify_token'])) {
                    throw new BadRequestHttpException('Notify token is not set');
                }

                if (Api::EXECCODE_EXPIRED_3DSECURE_AUTHENTICATION === $execCode) {
                    return new Response('OK', 200);
                }

                $notifyToken = !empty($extraData) ? $extraData['notify_token'] : '';

                if (!$token = $this->payum->getTokenStorage()->find($notifyToken)) {
                    throw new NotFoundHttpException(sprintf('A token with "%s" could not be found.', $notifyToken));
                }

                $gateway->execute($requestNotify = new Notify($token));

                $requestNotifyModel = $requestNotify->getModel();

                // Je ne supprime pas car à terme il faudra changer la logique par rapport à Payum et réactiver l'invalidation des tokens
                // $this->payum->getHttpRequestVerifier()->invalidate($getToken->getToken());

                if (Api::OPERATION_VOID === $requestNotifyModel['OPERATIONTYPE']) {
                    return new Response('Waiting void...', 200);
                }

                return new Response('OK', 200);

            default:
                throw new \LogicException();
        }
    }
}
