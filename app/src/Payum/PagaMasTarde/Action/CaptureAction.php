<?php

namespace App\Payum\PagaMasTarde\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private string $templateName
    ) {
    }

    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($renderTemplate = new RenderTemplate(
            $this->templateName,
            $this->getTemplateParameters($request)
        ));

        throw new HttpResponse($renderTemplate->getResult());
    }

    private function getTemplateParameters(Capture $request): array
    {
        $this->gateway->execute($httpRequest = new GetHttpRequest());

        $model = $request->getFirstModel();
        $secretKey = $model->getMethod()->getGatewayConfig()->getConfig()['secretKey'];
        $publicKey = $model->getMethod()->getGatewayConfig()->getConfig()['publicKey'];
        $orderId = $model->getId();
        $amount = $model->getAmount();
        $currency = $model->getCurrencyCode();
        $okUrl = $httpRequest->query['redirectUrl'] . '?status=completed&token=' . $model->getOrder()->getTokenValue();
        $nokUrl = $httpRequest->query['redirectUrl'] . '?status=failed&token=' . $model->getOrder()->getTokenValue();
        $cancelledUrl = $httpRequest->query['cancelUrl'];
        $callbackUrl = $model->getMethod()->getGatewayConfig()->getConfig()['callbackUrl'];
        $signature = hash('sha512', $secretKey . $publicKey . $orderId . $amount . $currency . $okUrl . $nokUrl . $callbackUrl . $cancelledUrl);
        $shippingAddress = $model->getOrder()->getShippingAddress();

        return [
            'orderId' => $orderId,
            'amount' => $amount,
            'currency' => $currency,
            'okUrl' => $okUrl,
            'nokUrl' => $nokUrl,
            'cancelledUrl' => $cancelledUrl,
            'callbackUrl' => $callbackUrl,
            'publicKey' => $publicKey,
            'signature' => $signature,
            'fullName' => sprintf('%s %s', $shippingAddress->getFirstName(), strtoupper((string) $shippingAddress->getLastName())),
            'email' => $model->getOrder()->getCustomer()->getEmail(),
            'street' => $shippingAddress->getStreet(),
            'city' => $shippingAddress->getCity(),
            'zipcode' => $shippingAddress->getPostcode(),
        ];
    }

    public function supports($request)
    {
        return
            $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess
        ;
    }
}
