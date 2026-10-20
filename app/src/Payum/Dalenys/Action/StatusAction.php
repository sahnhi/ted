<?php

namespace App\Payum\Dalenys\Action;

use App\Payum\Dalenys\Api;
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
        } elseif (Api::EXECCODE_SUCCESSFUL === $model['EXECCODE'] && Api::OPERATION_PAYMENT === $model['OPERATIONTYPE']) {
            $request->markCaptured();
        } elseif (Api::EXECCODE_TIME_OUT === $model['EXECCODE'] && Api::OPERATION_PAYMENT === $model['OPERATIONTYPE']) {
            $request->markUnknown();
        } elseif (Api::EXECCODE_SUCCESSFUL === $model['EXECCODE'] && Api::OPERATION_REFUND === $model['OPERATIONTYPE'] || Api::OPERATION_VOID === $model['OPERATIONTYPE']) {
            // Pour Sahème : J'ai commenté cette fonction car on va la réactiver plus tard en attendant de développer
            // une solution pour répercuter les remboursements sur les BIS

            // TODO: Réactiver cette fonctionnalité lorsque la solution pour répercurter les remboursements sur les BIS sera dispo
            // $request->markRefunded();
        } else {
            $request->markFailed();
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess
        ;
    }
}
