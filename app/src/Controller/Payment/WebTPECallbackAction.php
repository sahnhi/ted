<?php

namespace App\Controller\Payment;

use Payum\Core\Payum;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WebTPECallbackAction
{
    public function __construct(
        private readonly Payum $payum,
        private readonly PaymentRepositoryInterface $paymentRepository
    ) {
    }

    public function __invoke(Request $request, string $action): Response
    {
        if (!$request->query->get('REQUESTID') && !$request->query->get('AMOUNT')) {
            throw new BadRequestHttpException('Dalenys request is invalid');
        }

        $gateway = $this->payum->getGateway('be2bill_webtpe');

        $payment = $this->paymentRepository->findByLikeRequestIdAndAmountInDetails($request->query->get('REQUESTID'), $request->query->get('AMOUNT'));

        if (!$payment) {
            throw new BadRequestHttpException('Request doesn\'t exist');
        }

        $details = $payment->getDetails();
        $extraData = json_decode((string) $details['EXTRADATA'], true);

        switch ($action) {
            case 'capture':
                if (!isset($extraData['capture_token'])) {
                    throw new BadRequestHttpException('Capture token is not set');
                }

                $gateway->execute($getToken = new GetToken($extraData['capture_token']));

                $gateway->execute(new Capture($getToken->getToken()));

                $this->payum->getHttpRequestVerifier()->invalidate($getToken->getToken());

                return RedirectResponse::create($getToken->getToken()->getAfterUrl());
            case 'notify':
                if (!isset($extraData['notify_token'])) {
                    throw new BadRequestHttpException('Notify token is not set');
                }

                $gateway->execute($getToken = new GetToken($extraData['notify_token']));
                $gateway->execute(new Notify($getToken->getToken()));

                $this->payum->getHttpRequestVerifier()->invalidate($getToken->getToken());

                return Response::create();

            default:
                throw new \LogicException();
        }
    }
}
