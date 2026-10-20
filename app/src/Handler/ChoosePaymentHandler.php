<?php

namespace App\Handler;

use App\Business\Command\ChoosePayment;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Core\Payment\Provider\OrderPaymentProviderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface as PaymentInterfaceAlias;
use Sylius\Component\Payment\PaymentTransitions;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class ChoosePaymentHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly FactoryInterface $stateMachineFactory,
        private readonly TranslatorInterface $translator,
        private readonly OrderPaymentProviderInterface $paymentProvider
    ) {
    }

    /**
     * @throws SMException
     */
    public function handle(ChoosePayment $choosePayment): PaymentInterface
    {
        /** @var OrderInterface $cart */
        $cart = $this->orderRepository->findOneBy(['tokenValue' => $choosePayment->getToken()]);
        Assert::notNull($cart, $this->translator->trans('app.api.order.payment.error.cart_not_found'));

        $stateMachine = $this->stateMachineFactory->get($cart, OrderCheckoutTransitions::GRAPH);

        // remove all old "new" payments
        /** @var PaymentInterface $payment */
        foreach ($cart->getPayments() as $payment) {
            Assert::true(
                PaymentInterfaceAlias::STATE_COMPLETED !== $payment->getState(),
                $this->translator->trans('app.api.order.payment.error.payment_completed')
            );
            Assert::true(
                PaymentInterfaceAlias::STATE_PROCESSING !== $payment->getState(),
                $this->translator->trans('app.api.order.payment.error.payment_processing')
            );
            if (PaymentInterfaceAlias::STATE_NEW === $payment->getState()) {
                $cart->removePayment($payment);
            }
        }

        /** @var PaymentInterface $payment */
        $payment = $cart->getLastPaymentByStateOrReturnLast(PaymentInterfaceAlias::STATE_CART);

        if (!$payment instanceof PaymentInterface) {
            $payment = $this->paymentProvider->provideOrderPayment($cart, PaymentInterfaceAlias::STATE_CART);
            $cart->addPayment($payment);
        }

        $statePaymentMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);
        if (!$statePaymentMachine->can(PaymentTransitions::TRANSITION_CREATE)) {
            $payment = $this->paymentProvider->provideOrderPayment($cart, PaymentInterfaceAlias::STATE_CART);
            $cart->addPayment($payment);
        }

        Assert::notNull(
            $payment,
            $this->translator->trans('app.api.order.payment.error.payment_not_found')
        );

        Assert::true(
            PaymentInterfaceAlias::STATE_COMPLETED !== $payment->getState(),
            $this->translator->trans('app.api.order.payment.error.payment_completed')
        );
        Assert::true(
            PaymentInterfaceAlias::STATE_PROCESSING !== $payment->getState(),
            $this->translator->trans('app.api.order.payment.error.payment_processing')
        );
        Assert::true(
            PaymentInterfaceAlias::STATE_REFUNDED !== $payment->getState(),
            $this->translator->trans('app.api.order.payment.error.payment_refunded')
        );
        Assert::true(
            $stateMachine->can(OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT),
            $this->translator->trans('app.api.order.payment.error.invalid_payment_method')
        );

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => $choosePayment->getCode()]);
        Assert::notNull(
            $paymentMethod,
            $this->translator->trans('app.api.order.payment.error.payment_method_not_found')
        );
        Assert::notNull(
            $cart->getLastPaymentByStateOrReturnLast(PaymentInterfaceAlias::STATE_CART),
            $this->translator->trans('app.api.order.payment.error.payment_not_found')
        );

        $payment->setMethod($paymentMethod);
        $payment->setDetails([]);
        $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT);

        $statePaymentMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

        if (PaymentInterfaceAlias::STATE_NEW !== $statePaymentMachine->getState()) {
            $statePaymentMachine->apply(PaymentTransitions::TRANSITION_CREATE);
        }

        return $payment;
    }
}
