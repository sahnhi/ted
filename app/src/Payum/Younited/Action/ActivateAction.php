<?php

namespace App\Payum\Younited\Action;

use App\Bridge\YounitedBridgeInterface;
use App\Legacy\Younited\YounitedPayment;
use App\Payum\Core\Request\Activate;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Sylius\Component\Core\Model\PaymentInterface;

final class ActivateAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    /** @var YounitedBridgeInterface */
    private $younitedBridge;

    public function setApi($younitedBridge)
    {
        if (!$younitedBridge instanceof YounitedBridgeInterface) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->younitedBridge = $younitedBridge;
    }

    /**
     * @param Activate $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $payment PaymentInterface */
        $payment = $request->getModel();

        $model = ArrayObject::ensureArrayObject($payment->getDetails());

        $clientId = $this->younitedBridge->getClientId();
        $clientSecret = $this->younitedBridge->getClientSecret();
        $resource = $this->younitedBridge->getResource();

        $younited = $this->younitedBridge->createYounited($clientId, $clientSecret, $resource);

        $environment = $this->younitedBridge->getEnvironment();

        $activateContract = new YounitedPayment(
            $younited,
            $environment,
            '',
            $payment->getOrder()
        );

        if (empty($model['bearer']) || !empty($model['bearer'] && $model['bearer']['expires_on'] < time())) {
            $model->replace($activateContract->authorize());
        }

        $payment->setDetails((array) $model->toUnsafeArray());

        $request->setModel($model);

        $accessToken = $request->getModel()['bearer']['access_token'];
        $response = $activateContract->contractActivate($accessToken);

        return [$response['statusCode'], $response['errors']];
    }

    public function supports($request)
    {
        return $request instanceof Activate && $request->getModel() instanceof PaymentInterface;
    }
}
