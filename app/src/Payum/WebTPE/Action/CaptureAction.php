<?php

namespace App\Payum\WebTPE\Action;

use App\Payum\WebTPE\Api;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Payment\Model\Payment;
use Sylius\Component\Payment\Model\PaymentInterface;

/**
 * @property Api $api
 */
class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function __construct(
        private ManagerRegistry $doctrine
    ) {
        $this->apiClass = Api::class;
    }

    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $payment = $request->getFirstModel();

        $result = $this->api->payment($model->toUnsafeArray());

        $model->replace((array) $result);

        if (!empty($result->REDIRECTURL) && Api::EXECCODE_WAITING_PROVIDER === $model['EXECCODE']) {
            $this->handleProcessing($payment);

            throw new HttpRedirect($result->REDIRECTURL);
        }
    }

    public function supports($request)
    {
        return
            $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess;
    }

    private function handleProcessing(Payment $payment)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        $em->transactional(function (EntityManager $em) use ($payment): void {
            $em->lock($payment, LockMode::PESSIMISTIC_WRITE);
            $em->refresh($payment);

            if (PaymentInterface::STATE_NEW !== $payment->getState()) {
                return;
            }

            $payment->setState(PaymentInterface::STATE_PROCESSING);
        });
    }
}
