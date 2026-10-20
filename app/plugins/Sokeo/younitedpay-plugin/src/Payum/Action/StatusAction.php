<?php

declare(strict_types=1);

namespace Sokeo\SyliusYounitedpayPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sokeo\SyliusYounitedpayPlugin\Api\YounitedpayApiInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $status = isset($model['status']) ? $model['status'] : null;

        if (null === $status) {
            $request->markNew();

            return;
        }

        if (PaymentInterface::STATE_FAILED === $status && YounitedpayApiInterface::REASON_CODE_SIGNED !== $model['ReasonCode']) {
            $request->markFailed();

            return;
        }

        if (PaymentInterface::STATE_CANCELLED === $status && YounitedpayApiInterface::REASON_CODE_SIGNED !== $model['ReasonCode']) {
            $request->markCanceled();

            return;
        }

        if (PaymentInterface::STATE_COMPLETED === $status && YounitedpayApiInterface::REASON_CODE_SIGNED === $model['ReasonCode']) {
            $request->markCaptured();

            return;
        }

        $request->markUnknown();
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface
            && $request->getFirstModel() instanceof PaymentInterface
        ;
    }
}
