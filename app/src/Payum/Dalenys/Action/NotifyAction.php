<?php

namespace App\Payum\Dalenys\Action;

use App\Message\DalenysNotify;
use App\Payum\Dalenys\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Symfony\Component\Messenger\MessageBusInterface;

class NotifyAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct(
        private MessageBusInterface $bus
    ) {
        $this->apiClass = Api::class;
    }

    /**
     * @param $request Notify
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (!$this->api->verifyHash($httpRequest->request)) {
            throw new HttpResponse('The notification is invalid', 400);
        }

        $details->replace($httpRequest->request);

        $payment = $request->getFirstModel();

        $this->bus->dispatch(new DalenysNotify($payment->getId(), $details, $httpRequest->request));
    }

    public function supports($request): bool
    {
        return
            $request instanceof Notify
            && $request->getModel() instanceof \ArrayAccess;
    }
}
