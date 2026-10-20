<?php

namespace App\Controller\Api\Order;

use App\Modifier\OrderGiftCardAmountModifier;
use Doctrine\Persistence\ObjectManager;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Payment\Model\PaymentInterface as PaymentInterfaceAlias;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final class ModifyAction
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly ObjectManager $orderManager,
        private readonly FactoryInterface $stateMachineFactory,
        private readonly OrderGiftCardAmountModifier $orderGiftCardAmountModifier
    ) {
    }

    /**
     * @throws SMException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->attributes->get('tokenValue');
        /** @var OrderInterface $order */
        $order = $this->orderRepository->getOneByTokenValueHydrated($token);
        Assert::notNull($order, sprintf('Order with token value "%s" and conditions does not exists', $token));

        if (OrderPaymentStates::STATE_AWAITING_PAYMENT === $order->getPaymentState()
            || OrderCheckoutStates::STATE_COMPLETED === $order->getCheckoutState()
            || OrderCheckoutStates::STATE_PAYMENT_SELECTED === $order->getCheckoutState()) {
            $this->orderGiftCardAmountModifier->resetIfDisable($order);

            $stateMachine = $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH);
            $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT);
            $payment = $order->getLastPayment(PaymentInterfaceAlias::STATE_CART);
            $payment->setDetails([]);

            $this->orderManager->flush();
            $this->orderManager->clear();
        }

        try {
            return new JsonResponse(
                $token,
                Response::HTTP_OK,
                ['Content-Type' => 'application/ld+json']
            );
        } catch (\InvalidArgumentException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
