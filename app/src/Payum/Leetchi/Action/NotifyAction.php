<?php

namespace App\Payum\Leetchi\Action;

use App\Doctrine\Entity\Sylius\PaymentMethod;
use App\Services\Erp\ForwardToErp;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\Payment;

class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private ManagerRegistry $doctrine,
        private ForwardToErp $forwardToErp
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var Payment $payment */
        $payment = $request->getFirstModel();
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $payment->getMethod();
        $options = $paymentMethod->getGatewayConfig()->getConfig();

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (false === $this->isRequestValid($httpRequest->request, $options['apiPassword'])) {
            $details = (array) $httpRequest->request;
            $details['error'] = 'request signature is not valid';

            $payment->setDetails($details);

            throw new HttpResponse('');
        }

        $this->gateway->execute($status = new GetStatus($httpRequest->request));

        if ($status->isNew()) {
            // seems request is not valid, see payment details
            $payment->setDetails($httpRequest->request);

            throw new HttpResponse('');
        }

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        $em->transactional(function (EntityManager $em) use ($payment, $status, $httpRequest): void {
            $em->lock($payment, LockMode::PESSIMISTIC_WRITE);
            $em->refresh($payment);

            $payment->setState($status->getValue());
            $payment->setDetails($httpRequest->request);

            if ('completed' === $status->getValue()) {
                $order = $payment->getOrder();
                $order->setPaymentState('paid');

                // event not detected so called by service
                $this->forwardToErp->postComplete($order);
            }
        });

        // Default NotifyController's response code is 204 but Leetchi accepts only 200
        throw new HttpResponse('');
    }

    private function isRequestValid(array $request, string $secret): bool
    {
        $request = ArrayObject::ensureArrayObject($request);

        $merchantId = (string) $request['merchant_id'];
        $transactionId = (string) $request['lma_transaction_id'];
        $amount = (string) $request['lma_amount'];
        $status = (string) $request['status'];
        $md5Sig = (string) $request['md5sig'];

        $signature = strtoupper(md5($merchantId . $transactionId . strtoupper(md5($secret)) . $amount . $status));

        return $signature === $md5Sig;
    }

    /**
     * @return bool
     */
    public function supports($request)
    {
        return
            $request instanceof Notify
            && $request->getModel() instanceof \ArrayAccess
        ;
    }
}
