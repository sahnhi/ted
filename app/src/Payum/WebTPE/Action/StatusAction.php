<?php

namespace App\Payum\WebTPE\Action;

use App\Payum\WebTPE\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());

        if (null === $model['EXECCODE']) {
            $request->markNew();
        } elseif (Api::EXECCODE_WAITING_PROVIDER === $model['EXECCODE']) {
            $request->markPending();
        } elseif (Api::EXECCODE_SUCCESSFUL === $model['EXECCODE']) {
            $request->markCaptured();
        } elseif (Api::EXECCODE_ABORTED_TRANSACTION === $model['EXECCODE']) {
            $request->markCanceled();
        } elseif (Api::EXECCODE_TIME_OUT === $model['EXECCODE']) {
            $request->markUnknown();
        } else {
            $request->markFailed();
        }

        $request->setModel($model);
    }

    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess
        ;
    }
}
