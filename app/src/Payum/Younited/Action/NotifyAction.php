<?php

namespace App\Payum\Younited\Action;

use App\Bridge\YounitedBridgeInterface;
use App\Legacy\Younited\YounitedApiInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachine;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Order\OrderTransitions;
use Webmozart\Assert\Assert;

final class NotifyAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    /** @var YounitedBridgeInterface */
    private $younitedBridge;

    public function __construct(
        private FactoryInterface $stateMachineFactory
    ) {
    }

    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if (false !== $this->younitedBridge->getDecisionResult()) {
            /** @var PaymentInterface $payment */
            $payment = $request->getFirstModel();

            $details = ArrayObject::ensureArrayObject($request->getModel());

            $this->gateway->execute($httpRequest = new GetHttpRequest());

            $details->replace($httpRequest->request);

            $payment->setDetails((array) $details);

            Assert::isInstanceOf($payment, PaymentInterface::class);

            $details['status'] = PaymentInterface::STATE_CANCELLED;

            if (YounitedApiInterface::DECISION_RESULT_ACCEPTED === $this->younitedBridge->getDecisionResult()) {
                $details['status'] = PaymentInterface::STATE_COMPLETED;
            } elseif (YounitedApiInterface::REASON_CODE_NO_OFFER === $this->younitedBridge->getReasonCode()) {
                $details['status'] = PaymentInterface::STATE_FAILED;
            }

            $redirect = $details['redirectUrl'];
            if (PaymentInterface::STATE_COMPLETED !== $details['status']) {
                if (PaymentInterface::STATE_CANCELLED === $details['status']
                    && (YounitedApiInterface::REASON_CODE_REFUSAL === $this->younitedBridge->getReasonCode()
                    || YounitedApiInterface::REASON_CODE_BOC_CANCEL === $this->younitedBridge->getReasonCode()
                    || YounitedApiInterface::REASON_CODE_CANCEL === $this->younitedBridge->getReasonCode())
                ) {
                    $details['cancelledByYounited'] = true;
                }

                $details->replace($httpRequest->request);

                $payment->setDetails((array) $details);

                /** @var StateMachine $orderStateMachine */
                $orderStateMachine = $this->stateMachineFactory->get($payment->getOrder(), OrderTransitions::GRAPH);

                if (isset($details['cancelledByYounited']) && $orderStateMachine->can(OrderTransitions::TRANSITION_CANCEL)) {
                    $orderStateMachine->apply(OrderTransitions::TRANSITION_CANCEL);
                }

                $redirect = $details['cancelUrl'];
            }

            throw new HttpResponse(json_encode(['url' => $redirect]), 200, ['Content-Type' => 'application/json']);
        }
    }

    public function setApi($younitedBridge)
    {
        if (!$younitedBridge instanceof YounitedBridgeInterface) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->younitedBridge = $younitedBridge;
    }

    public function supports($request)
    {
        return
            $request instanceof Notify
            && $request->getModel() instanceof \ArrayObject
        ;
    }
}
