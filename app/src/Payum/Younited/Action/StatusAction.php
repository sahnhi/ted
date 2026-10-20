<?php

namespace App\Payum\Younited\Action;

use App\Legacy\Younited\YounitedApiInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class StatusAction implements ActionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    /**
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $status = $model['status'] ?? null;

        if (null === $status) {
            $request->markNew();

            return;
        }

        if (PaymentInterface::STATE_FAILED === $status && YounitedApiInterface::REASON_CODE_SIGNED !== $model['ReasonCode']) {
            $request->markFailed();

            return;
        }

        if (PaymentInterface::STATE_CANCELLED === $status && YounitedApiInterface::REASON_CODE_SIGNED !== $model['ReasonCode']) {
            $request->markCanceled();

            return;
        }

        if (PaymentInterface::STATE_COMPLETED === $status && YounitedApiInterface::REASON_CODE_SIGNED === $model['ReasonCode']) {
            $request->markCaptured();

            return;
        }

        $request->markUnknown();

        return;
    }

    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess
        ;
    }
}
