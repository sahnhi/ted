<?php

namespace App\Legacy\Younited;

use App\Bridge\YounitedBridgeInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use App\Payum\Core\Request\Activate;
use App\Payum\Younited\Action\ActivateAction;
use App\Services\Sentry\ReportingInterface;
use Payum\Core\Payum;
use Sentry;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Webmozart\Assert\Assert;

class YounitedPaymentProcessor implements YounitedPaymentProcessorInterface
{
    public function __construct(
        private readonly RepositoryInterface $paymentRepository,
        private readonly RepositoryInterface $orderRepository,
        private readonly Payum $payum,
        private readonly YounitedBridgeInterface $younitedBridge,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function activateContract($paymentId)
    {
        $payment = $this->paymentRepository->find($paymentId);

        /* @var PaymentInterface $payment */
        Assert::isInstanceOf($payment, PaymentInterface::class);

        if (!isset($payment->getDetails()['activateToken'])) {
            throw new TokenNotFoundException('Activate Token not found.');
        }

        $gatewayName = $payment->getMethod()->getGatewayConfig()->getGatewayName();
        $gateway = $this->payum->getGateway($gatewayName);

        $action = new ActivateAction();
        $action->setGateway($gateway);

        $action->setApi($this->younitedBridge);

        return $action->execute(new Activate($payment));
    }

    public function activateAllByOrder(Order $order)
    {
        $order = $this->getInitialOrBis($order);
        $payments = $order->getPayments();

        foreach ($payments as $payment) {
            if (null === $payment->getMethod()) {
                continue;
            }

            if (PaymentMethodInterface::YOUNITED === $payment->getMethod()->getCode() && PaymentInterface::STATE_COMPLETED === $payment->getState()) {
                try {
                    $this->activateContract($payment->getId());
                    $order->setYounitedActivated(true);
                    $this->orderRepository->add($order);
                } catch (\Exception $exception) {
                    $this->reporting->reportIssue(
                        $exception,
                        Sentry\Severity::warning(),
                        [
                            'payment_method' => 'Younited',
                            'order_number' => $order->getNumber(),
                        ],
                        ['order_payment' => $payment]
                    );
                }
            }
        }
    }

    private function getInitialOrBis(Order $order)
    {
        if (null !== $order->getParentNumber() && 'bis' === $order->getChannel()->getCode()) {
            $parent = $this->orderRepository->findOneLikeNumber($order->getParentNumber());

            if (null !== $parent) {
                return $parent;
            }
        }

        return $order;
    }
}
