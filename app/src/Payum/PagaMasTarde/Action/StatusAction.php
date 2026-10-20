<?php

namespace App\Payum\PagaMasTarde\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false === property_exists($model, 'paid')) {
            $request->markNew();

            return;
        }

        switch ((int) $model['paid']) {
            case true:
                $request->markCaptured();
                break;
            case false:
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
