<?php

namespace App\Payum\Printemps\Action;

use App\Doctrine\Entity\Sylius\Order;
use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistryInterface;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Offline\Constants;
use Sylius\Bundle\ThemeBundle\Translation\ThemeAwareTranslator;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Payment\Provider\OrderPaymentProviderInterface;
use Sylius\Component\Payment\Model\Payment;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private readonly string $templateName,
        private readonly MailerInterface $mailer,
        private readonly RouterInterface $router,
        private readonly FlashBagInterface $flashBag,
        private readonly ThemeAwareTranslator $translator,
        private readonly ManagerRegistry $doctrine,
        private readonly OrderPaymentProviderInterface $orderPaymentProvider,
        private readonly EmailConfigurationRegistryInterface $emailConfigurationRegistry
    ) {
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EmailConfigurationNotFoundException
     */
    public function execute($request): void
    {
        /* @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        /** @var Payment $payment */
        $payment = $request->getFirstModel();

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));

        if (!empty($_POST)) {
            $barcode = htmlspecialchars((string) $_POST['barcode']);

            $model['codeBarre'] = $barcode;
            $model[Constants::FIELD_STATUS] = Constants::STATUS_AUTHORIZED;

            $model->replace($model);

            $senders = $this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::SUPPORT);
            $recipients = $this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::PRINTEMPS_PAYMENT_CAPTURE);

            $message = (new Email())
                ->subject(sprintf('Commande %s', $payment->getMethod()?->getName()))
                ->from(new Address($senders->getFrom(), $senders->getFromName()))
                ->to(...$recipients->getPrincipals())
                ->cc(...$recipients->getCcs())
                ->text(
                    $this->router->generate('sylius_admin_order_show_custom', ['id' => $payment->getOrder()->getId()], UrlGenerator::ABSOLUTE_URL),
                    'UTF-8'
                );
            try {
                $this->mailer->send($message);
            } catch (TransportExceptionInterface) {
                $this->flashBag->add(
                    'error',
                    $this->translator->trans('app.ui.printemps_error_mail')
                );
            }
        } else {
            if (!empty($httpRequest->query['cancelled']) && '1' === $httpRequest->query['cancelled']) {
                $this->handleCancelled($payment);
                $this->handleProviderOrderPayment($payment->getOrder());

                return;
            }

            $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, ['amount' => number_format($payment->getAmount() / 100, 2, '.', ' ') . ' ' . $currency->name, 'gatewayName' => $payment->getMethod()->getName(), 'gatewayCode' => $payment->getMethod()->getCode(), 'orderNumber' => $payment->getOrder()->getNumber(), 'cancelUrl' => $this->router->generate('payum_capture_do', [
                'payum_token' => $request->getToken()->getHash(),
                'cancelled' => true,
            ]), ]));

            throw new HttpResponse($renderTemplate->getResult());
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess;
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

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function handleProviderOrderPayment(Order $order)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        $newPayment = $this->orderPaymentProvider->provideOrderPayment($order, PaymentInterface::STATE_NEW);
        $order->addPayment($newPayment);

        $em->persist($order);
        $em->flush();
    }
}
