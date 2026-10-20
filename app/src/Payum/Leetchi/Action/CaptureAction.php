<?php

namespace App\Payum\Leetchi\Action;

use App\Doctrine\Entity\Sylius\PaymentMethod;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Component\Payment\Model\Payment;
use Sylius\Component\Payment\Model\PaymentInterface;

class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function __construct(
        private ManagerRegistry $doctrine,
        private string $templateName
    ) {
    }

    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        ArrayObject::ensureArrayObject($request->getModel());
        /** @var Payment $payment */
        $payment = $request->getFirstModel();
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $payment->getMethod();
        $options = $paymentMethod->getGatewayConfig()->getConfig();

        $environment = 'www';
        if (isset($options['sandbox']) && true === $options['sandbox']) {
            $environment = 'www-preprod';
        }

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (isset($httpRequest->query['authorized']) && '1' === $httpRequest->query['authorized']) {
            $this->handleAuthorized($payment);

            return;
        } elseif (isset($httpRequest->query['canceled']) && '1' === $httpRequest->query['canceled']) {
            $this->handleCancelled($payment);

            return;
        }

        $statusToken = $this->tokenFactory->createNotifyToken($request->getToken()->getGatewayName(), $payment);

        $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, ['email' => $options['merchantEmail'], 'orderId' => $payment->getId(), 'amount' => $payment->getAmount(), 'statusUrl' => $statusToken->getTargetUrl(), 'returnUrl' => $httpRequest->query['redirectUrl'] . '&status=pending', 'cancelUrl' => $httpRequest->query['cancelUrl'], 'environment' => $environment]));

        throw new HttpResponse($renderTemplate->getResult());
    }

    public function supports($request)
    {
        return
            $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess
        ;
    }

    private function handleAuthorized(Payment $payment)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        $em->transactional(function (EntityManager $em) use ($payment): void {
            $em->lock($payment, LockMode::PESSIMISTIC_WRITE);
            $em->refresh($payment);

            if (PaymentInterface::STATE_NEW !== $payment->getState()) {
                return;
            }

            $payment->setState(PaymentInterface::STATE_AUTHORIZED);
        });
    }

    private function handleCancelled(Payment $payment)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        $em->transactional(function (EntityManager $em) use ($payment): void {
            $em->lock($payment, LockMode::PESSIMISTIC_WRITE);
            $em->refresh($payment);

            if (PaymentInterface::STATE_NEW !== $payment->getState()) {
                return;
            }

            $payment->setState(PaymentInterface::STATE_CANCELLED);
        });
    }
}
