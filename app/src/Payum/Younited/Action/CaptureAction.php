<?php

namespace App\Payum\Younited\Action;

use App\Bridge\YounitedBridgeInterface;
use App\Legacy\Younited\YounitedPayment;
use App\Services\Sentry\ReportingInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Security\TokenInterface;
use Sentry;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class CaptureAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    /** @var YounitedBridgeInterface */
    private $younitedBridge;

    public function __construct(
        private Payum $payum,
        private string $templateName,
        private string $errorTemplateName,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function setApi($younitedBridge)
    {
        if (!$younitedBridge instanceof YounitedBridgeInterface) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->younitedBridge = $younitedBridge;
    }

    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        /** @var OrderInterface $order */
        $order = $payment->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (empty($model['redirectUrl']) || empty($model['cancelUrl'])) {
            $model['redirectUrl'] = !empty($httpRequest->query['redirectUrl']) ? $httpRequest->query['redirectUrl'] . '&status=completed' : '';
            $model['cancelUrl'] = !empty($httpRequest->query['cancelUrl']) ? $httpRequest->query['cancelUrl'] . '?younited=failed' : '';
        }

        /** @var TokenInterface $token */
        $token = $request->getToken();

        $notifyToken = $this->createNotifyToken($token->getGatewayName(), $token->getDetails());
        $activateToken = $this->createActivateToken($token->getGatewayName(), $token->getDetails());

        if (null !== $activateToken) {
            $model['activateToken'] = $activateToken->getHash();
        }

        $model['captureToken'] = $token->getHash();

        if (null !== $notifyToken) {
            $model['notifyToken'] = $notifyToken->getHash();
        }

        $clientId = $this->younitedBridge->getClientId();
        $clientSecret = $this->younitedBridge->getClientSecret();
        $resource = $this->younitedBridge->getResource();

        $younited = $this->younitedBridge->createYounited($clientId, $clientSecret, $resource);

        $environment = $this->younitedBridge->getEnvironment();

        $callbackUrl = $notifyToken->getTargetUrl();

        if (!empty($callbackUrl)) {
            $model['callbackUrl'] = $callbackUrl;
        }

        $requestCredit = new YounitedPayment(
            $younited,
            $environment,
            $callbackUrl,
            $order
        );

        if (empty($model['bearer']) || !empty($model['bearer'] && $model['bearer']['expires_on'] < time())) {
            $model->replace($requestCredit->authorize());
        }

        $payment->setDetails((array) $model->toUnsafeArray());

        $request->setModel($model);

        $accessToken = $request->getModel()['bearer']['access_token'];
        $response = $requestCredit->sessionInitialize($accessToken);

        if (200 === $response['statusCode'] && !empty($response['redirectUrl'])) {
            $this->reporting->reportIssue(
                'Younited capture action',
                Sentry\Severity::warning(),
                [
                    'payment_method' => 'Younited',
                    'order_number' => $order->getNumber(),
                ],
                ['payload' => $response]
            );

            $this->gateway->execute($template = new RenderTemplate($this->templateName, [
                'redirectUrl' => $response['redirectUrl'],
            ]));

            throw new HttpResponse($template->getResult());
        }

        if (200 !== $response['statusCode'] && !empty($response['errors'])) {
            $this->gateway->execute($errorTemplate = new RenderTemplate($this->errorTemplateName, [
                'response' => $response,
            ]));

            $this->reporting->reportIssue(
                'Younited error',
                Sentry\Severity::warning(),
                [
                    'payment_method' => 'Younited',
                    'order_number' => $order->getNumber(),
                ],
                ['payload' => $response]
            );

            throw new HttpResponse($errorTemplate->getResult(), $response['statusCode']);
        }
    }

    /**
     * @return TokenInterface
     */
    private function createNotifyToken($gatewayName, $model)
    {
        return $this->payum->getTokenFactory()->createNotifyToken(
            $gatewayName,
            $model
        );
    }

    /**
     * @return TokenInterface
     */
    private function createActivateToken($gatewayName, $model)
    {
        return $this->payum->getTokenFactory()->createToken(
            $gatewayName,
            $model,
            'sylius_shop_order_thank_you'
        );
    }

    public function supports($request)
    {
        return $request instanceof Capture && $request->getModel() instanceof \ArrayAccess;
    }
}
