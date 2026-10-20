<?php

namespace App\Payum\Leetchi\Action;

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

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false === property_exists($model, 'status')) {
            $request->markNew();

            return;
        }

        switch ((int) $model['status']) {
            case 2:
                $request->markCaptured();
                break;
            case 0:
                $request->markPending();
                break;
            case -1:
                $request->markCanceled();
                break;
            case -2:
            default:
                $request->markFailed();
                break;
        }
    }

    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess
        ;
    }
}
