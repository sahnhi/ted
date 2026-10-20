<?php

namespace App\Payum\Adyen\Action;

use Adyen\AdyenException;
use App\Bridge\AdyenBridgeInterface;
use App\Services\Payment\SendAdyenPaymentLink;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class CaptureAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;

    /** @var AdyenBridgeInterface */
    private $api;

    /** @var GenericTokenFactoryInterface */
    private $tokenFactory;

    public function __construct(
        private readonly SendAdyenPaymentLink $sendPaymentLink,
    ) {
    }

    public function setApi($api): void
    {
        if (!$api instanceof AdyenBridgeInterface) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    public function setGenericTokenFactory(?GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    /**
     * @throws AdyenException
     */
    public function execute($request): void
    {
        /* @var Capture $request */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var TokenInterface $token */
        $token = $request->getToken();

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $payment = $request->getFirstModel();
        if (isset($model['amount']['value']) && $payment instanceof PaymentInterface) {
            $model->replace([
                'amount' => [
                    'value' => $payment->getAmount(),
                    'currency' => $payment->getCurrencyCode() ?? 'EUR',
                ],
            ]);
        }

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        $extraData = $model['extraData'] ? json_decode((string) $model['extraData'], true) : [];

        if (false === isset($extraData['captureToken']) && $token) {
            $extraData['captureToken'] = $token->getHash();
            $model['captureURL'] = $token->getTargetUrl();
        }

        if (false === isset($extraData['notifyToken']) && $token && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $token->getGatewayName(),
                $token->getDetails()
            );
            $extraData['notifyToken'] = $notifyToken->getHash();
            $model['notifyURL'] = $notifyToken->getTargetUrl();
        }

        if (false === isset($extraData['refundToken']) && $token && $this->tokenFactory) {
            $extraData['refundToken'] = $this->tokenFactory->createRefundToken(
                $token->getGatewayName(),
                $token->getDetails() ?? $model
            )->getHash();
        }

        $model['extraData'] = json_encode($extraData);

        if (!empty($httpRequest->query['redirectResult'])) {
            try {
                $paymentDetails = $this->api->serviceCheckout()->paymentsDetails([
                    'details' => [
                        'redirectResult' => $httpRequest->query['redirectResult'],
                    ],
                ]);

                $model['authResult'] = strtoupper((string) $paymentDetails['resultCode']);
            } catch (\Exception) {
                $model['authResult'] = AdyenBridgeInterface::EVENT_CODE_ERROR;
            }
        } elseif (!empty($httpRequest->request['data'])) {
            $data = $httpRequest->request['data'];
            $deviseFingerprinting = $httpRequest->request['deviseFingerprinting'];

            if (!empty($data['details'])) {
                $response = $this->api->serviceCheckout()->paymentsDetails($data);

                $model['authResult'] = strtoupper((string) $response['resultCode']);

                $model->replace((array) $response);
            } else {
                $data['reference'] = $model['merchantReference'];
                $data['merchantAccount'] = $this->api->getMerchantAccount();
                $data['amount'] = $model['amount'];
                $data['additionalData'] = [
                    'allow3DS2' => true,
                ];
                $data['channel'] = 'web';
                $data['returnUrl'] = $model['captureURL'];
                $customerId = str_pad((string) $model['customer']['id'], 3, '0', STR_PAD_LEFT);
                $data['shopperReference'] = $customerId;
                $data['shopperLocale'] = $model['order']['localeCode'];
                $data['shopperEmail'] = $model['customer']['email'];
                $data['shopperName'] = [
                    'firstName' => $model['customer']['firstName'],
                    'lastName' => $model['customer']['lastName'],
                    'gender' => 'UNKNOWN',
                ];
                $data['deviceFingerprint'] = $deviseFingerprinting;
                $data['merchantOrderReference'] = $model['merchantOrderReference'];
                $data['enableRecurring'] = false;

                if (!empty($model['billingAddress'])) {
                    $data['billingAddress'] = $model['billingAddress'];
                }

                if (!empty($model['deliveryAddress'])) {
                    $data['deliveryAddress'] = $model['deliveryAddress'];
                }

                $response = $this->api->serviceCheckout()->payments($data);

                $model->replace((array) $response);

                $model['authResult'] = strtoupper((string) $response['resultCode']);
            }

            if (array_key_exists('action', $response)) {
                $this->gateway->execute($template = new RenderTemplate('@App/Payum/Adyen/Capture/json.html.twig', [
                    'data' => $response,
                ]));

                throw new HttpResponse($template->getResult(), 200, ['Content-type' => 'application/json']);
            }

            $this->gateway->execute($template = new RenderTemplate('@App/Payum/Adyen/Capture/json.html.twig', [
                'data' => ['returnUrl' => $token->getAfterUrl()],
            ]));

            throw new HttpResponse($template->getResult(), 200, ['Content-type' => 'application/json']);
        } else {
            switch ($this->api->getMethodPayment()) {
                case AdyenBridgeInterface::PAYMENT_METHOD_TYPE_CB:
                    $paymentMethods = $this->api->serviceCheckout()->paymentMethods([
                        'merchantAccount' => $this->api->getMerchantAccount(),
                        'countryCode' => $model['order']['countryCode'],
                        'shopperLocale' => $model['order']['localeCode'],
                        'amount' => $model['amount'],
                        'channel' => 'Web',
                    ]);

                    $renderVars = [
                        'paymentMethods' => json_encode($paymentMethods),
                        'environment' => $this->api->getEnvironment(),
                        'clientKey' => $this->api->getClientKey(),
                        'captureURL' => $model['captureURL'],
                    ];
                    break;

                case AdyenBridgeInterface::PAYMENT_METHOD_TYPE_PAYPAL:
                    $renderVars = [
                        'environment' => $this->api->getEnvironment(),
                        'merchantId' => $this->api->getPaypalMerchantId(),
                        'amount' => json_encode($model['amount']),
                        'countryCode' => $model['order']['countryCode'],
                        'captureURL' => $model['captureURL'],
                    ];
                    break;

                case AdyenBridgeInterface::PAYMENT_METHOD_TYPE_PAYBYLINK:
                    $this->sendPaymentLink->send($model['merchantOrderReference'], $model['merchantReference'], $this->api->getMerchantAccount(), $this->api->getApiUrl(), $this->api->getApiKey());
                    $renderVars = [
                        'orderNumber' => $model['merchantOrderReference'],
                    ];
                    break;
                default:
                    throw new \LogicException();
            }

            $this->gateway->execute($template = new RenderTemplate('@App/Payum/Adyen/Capture/' . $this->api->getMethodPayment() . '.html.twig', $renderVars));

            throw new HttpResponse($template->getResult());
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess;
    }
}
