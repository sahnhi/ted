<?php

namespace App\Controller\Api\Order;

use App\Checker\PaymentMethodEligibleChecker;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Factory\View\PaymentMethodViewFactoryInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ListPaymentAction
{
    public function __construct(
        private readonly OrderRepository $cartRepository,
        private readonly PaymentMethodsResolverInterface $paymentMethodsResolver,
        private readonly PaymentMethodViewFactoryInterface $paymentMethodViewFactory,
        private readonly ViewHandlerInterface $viewHandler,
        private readonly PaymentMethodEligibleChecker $paymentMethodEligibleChecker
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var OrderInterface $cart */
        $cart = $this->cartRepository->findOneBy(['tokenValue' => $request->attributes->get('token')]);
        $payments = [];
        foreach ($cart->getPayments() as $payment) {
            $payments['payments'][] = $this->getPaymentMethods($payment, $cart->getTotal(), $cart->getLocaleCode());
        }

        return $this->viewHandler->handle(View::create($payments));
    }

    private function getPaymentMethods(PaymentInterface $payment, int $total, $locale): array
    {
        $rawPaymentMethods = [];
        /** @var PaymentMethodInterface $paymentMethod */
        foreach ($this->paymentMethodsResolver->getSupportedMethods($payment) as $paymentMethod) {
            if ($this->paymentMethodEligibleChecker->isPaymentEligible($total, $paymentMethod)) {
                $rawPaymentMethods['methods'][$paymentMethod->getCode()] = $this->paymentMethodViewFactory->create($paymentMethod, $locale);
            }
        }

        return $rawPaymentMethods;
    }
}
