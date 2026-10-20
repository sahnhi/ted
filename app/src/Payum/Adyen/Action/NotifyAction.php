<?php

namespace App\Payum\Adyen\Action;

use App\Bridge\AdyenBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Symfony\Component\HttpFoundation\Response;

final class NotifyAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /** @var AdyenBridgeInterface */
    protected $api;

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

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        $data = $httpRequest->request['notificationItems'][0]['NotificationRequestItem'];

        if (!isset($data['merchantReference']) || empty($data['merchantReference'])) {
            $details['authResult'] = AdyenBridgeInterface::EVENT_CODE_ERROR;

            throw new HttpResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        if (!isset($details['merchantReference']) || ($details['merchantReference'] !== $data['merchantReference'])) {
            $details['authResult'] = AdyenBridgeInterface::EVENT_CODE_ERROR;

            throw new HttpResponse(null, Response::HTTP_PAYMENT_REQUIRED);
        }

        // TODO: À réactiver plus tard, apparement les anciennes commandes fait planter le notify, donc attendre quelques semaines avant réactivation (Désactivé le 27/09/2021)
        /* if (false === $this->api->verifyNotification($data)) {
            $details['authResult'] = AdyenBridgeInterface::EVENT_CODE_ERROR;

            throw new HttpResponse(null, Response::HTTP_FORBIDDEN);
        } */

        if (isset($data['eventCode'])) {
            if (AdyenBridgeInterface::EVENT_CODE_AUTHORISATION === $data['eventCode']) {
                if (true === filter_var($data['success'], FILTER_VALIDATE_BOOLEAN)) {
                    $details['authResult'] = $data['eventCode'];
                } else {
                    $details['authResult'] = AdyenBridgeInterface::EVENT_CODE_REFUSED;
                }
            }

            if (AdyenBridgeInterface::EVENT_CODE_REFUND === $data['eventCode']) {
                if (true === filter_var($data['success'], FILTER_VALIDATE_BOOLEAN)) {
                    $details['authResult'] = $data['eventCode'];
                } else {
                    $details['authResult'] = AdyenBridgeInterface::EVENT_CODE_REFUSED;
                }
            }
        }

        $details->replace((array) $data);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Notify
            && $request->getModel() instanceof \ArrayAccess
        ;
    }
}
