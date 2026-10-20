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
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Sylius\Component\Payment\Model\Payment;

class NotifyAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct(
        private ManagerRegistry $doctrine
    ) {
        $this->apiClass = Api::class;
    }

    /**
     * @param $request Notify
     *
     * @throws \Throwable
     */
    public function execute($request)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        $em->transactional(function (EntityManager $em) use ($request): void {
            /** @var Payment $payment */
            $payment = $request->getFirstModel();

            $em->lock($payment, LockMode::PESSIMISTIC_WRITE);
            $em->refresh($payment);

            $details = ArrayObject::ensureArrayObject($request->getModel());

            $this->gateway->execute($httpRequest = new GetHttpRequest());

            if (!$this->api->verifyHash($httpRequest->query)) {
                throw new HttpResponse('The notification is invalid', 400);
            }

            $details->replace($httpRequest->query);

            $payment->setDetails((array) $details);
        });

        throw new HttpResponse('OK', 200);
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
