<?php

namespace App\Payum\WebTPE\Action;

use Payum\Be2Bill\Action\ConvertPaymentAction as Be2billConvertPaymentAction;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;

class ConvertPaymentAction extends Be2billConvertPaymentAction implements GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    public function execute($request)
    {
        parent::execute($request);

        $payment = $request->getSource();
        $model = $request->getResult();

        $model['TERMINALID'] = $payment->getOrder()->getOrderStore()->getStore()->getTerminalId(); // 11476
        unset($model['CLIENTEMAIL'], $model['CLIENTIDENT']);

        // set extra tokens
        $extraData = isset($model['EXTRADATA']) && is_array($model['EXTRADATA'])
            ? json_decode($model['EXTRADATA'], true)
            : []
        ;

        if (!isset($extraData['capture_token']) && $request->getToken()->getHash()) {
            $extraData['capture_token'] = $request->getToken()->getHash();
        }

        if (!isset($extraData['notify_token']) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );

            $extraData['notify_token'] = $notifyToken->getHash();
        }

        $model['EXTRADATA'] = json_encode($extraData);

        $request->setResult((array) $model);
    }

    public function supports($request)
    {
        return
            $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && 'array' === $request->getTo()
        ;
    }
}
