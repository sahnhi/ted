<?php

namespace App\Payum\Adyen\Action;

use Adyen\AdyenException;
use App\Bridge\AdyenBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Payum;
use Payum\Core\Request\Refund;

final class RefundAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var AdyenBridgeInterface */
    protected $api;

    public function __construct(
        protected Payum $payum
    ) {
    }

    public function setApi($api): void
    {
        if (!$api instanceof AdyenBridgeInterface) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $modificationAmount = $model['refund'];

        $request = [
            'modificationAmount' => $modificationAmount,
            'merchantAccount' => $this->api->getMerchantAccount(),
            'originalReference' => $model['pspReference'],
        ];

        try {
            $refund = $this->api->serviceModification()->refund($request);
        } catch (AdyenException $exception) {
            throw new AdyenException($exception->getMessage());
        }

        $model['refundedAmount'] = array_merge([
            'latestPspReference' => $refund['pspReference'],
        ], $model['refundedAmount']);

        $refundHistory = !empty($model['refundHistory']) ? $model['refundHistory'] : [];

        $model['refundHistory'] = array_merge($refundHistory, [array_merge($modificationAmount, $refund)]);

        if ($model['refundedAmount']['value'] >= $model['amount']['value']) {
            $extraData = json_decode((string) $model['extraData'], true);
            $refundToken = $this->payum->getTokenStorage()->find($extraData['refundToken']);

            $this->payum->getHttpRequestVerifier()->invalidate($refundToken);
        }

        unset($model['refund']);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Refund
            && $request->getModel() instanceof \ArrayAccess
        ;
    }
}
