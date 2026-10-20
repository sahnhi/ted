<?php

namespace App\Controller\Api\Order;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\PaymentMethodInterface;
use App\Exception\OrderNotFound;
use App\Payum\Dalenys\Action\ConvertPaymentAction;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface as PaymentInterfaceAlias;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CompleteAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly Payum $payum,
        private readonly ConvertPaymentAction $convertPayment
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->attributes->get('tokenValue');

        $content = json_decode($request->getContent(), true);
        $order = $this->orderRepository->findOneBy(['tokenValue' => $token]);
        if (!$order instanceof Order) {
            throw new OrderNotFound(sprintf('Order with token "%s" does not exist.', $token));
        }

        if (OrderInterface::STATE_CANCELLED === $order->getState()) {
            throw new \Exception('Order is cancelled');
        }

        $request->getSession()->set('sylius_order_id', $order->getId());
        $payment = $order->getLastPayment(PaymentInterfaceAlias::STATE_CART);

        $redirectParamsPayment = [
            'channelCode' => $order->getChannel()->getCode(),
            'localeCode' => $order->getLocaleCode(),
        ];

        $redirectParams = [
            'localeCode' => $order->getLocaleCode(),
            'cancelUrl' => $content['cancelUrl'] ?? '',
            'redirectUrl' => ($content['redirectUrl'] ?? '') . '?token=' . $token,
        ];
        $token = $this->provideTokenBasedOnPayment($payment, $redirectParamsPayment);
        $return = [
            'captureUrl' => $token->getTargetUrl() . '?' . http_build_query($redirectParams),
        ];

        if (PaymentMethodInterface::BE2BILL_NTIMES === $payment->getMethod()->getCode()) {
            $return = array_merge(['amounts' => current($this->convertPayment->calculateAmounts($payment->getAmount() / 100))], $return);
        }

        if (PaymentMethodInterface::BE2BILL_NTIMES_DELAYED === $payment->getMethod()->getCode()) {
            $return = array_merge(['amounts' => current($this->convertPayment->calculateAmountsDelayed($payment->getAmount() / 100))], $return);
        }

        return new JsonResponse(
            $return,
            Response::HTTP_OK,
            ['Content-Type' => 'application/ld+json']
        );
    }

    private function provideTokenBasedOnPayment(PaymentInterface $payment, array $redirectParamsPayment): TokenInterface
    {
        $redirectUrl = 'shop_api_order_after_pay';

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        if (isset($gatewayConfig->getConfig()['use_authorize']) && true === (bool) $gatewayConfig->getConfig()['use_authorize']) {
            $token = $this->payum->getTokenFactory()->createAuthorizeToken($gatewayConfig->getGatewayName(), $payment, $redirectUrl, $redirectParamsPayment);
        } else {
            $token = $this->payum->getTokenFactory()->createCaptureToken($gatewayConfig->getGatewayName(), $payment, $redirectUrl, $redirectParamsPayment);
        }

        return $token;
    }
}
