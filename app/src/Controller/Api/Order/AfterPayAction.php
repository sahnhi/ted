<?php

namespace App\Controller\Api\Order;

use App\Business\Command\CompleteOrder;
use App\Doctrine\Repository\Sylius\PaymentRepositoryInterface;
use App\Services\Erp\ForwardToErp;
use League\Tactician\CommandBus;
use Payum\Core\Payum;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use SM\Factory\Factory as StateMachineFactory;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Bundle\PayumBundle\Factory\ResolveNextRouteFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AfterPayAction
{
    public function __construct(
        private readonly Payum $payum,
        private readonly GetStatusFactoryInterface $getStatusFactory,
        private readonly ResolveNextRouteFactoryInterface $resolveNextRouteFactory,
        private readonly \Twig\Environment $templating,
        private readonly CommandBus $commandBus,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly ForwardToErp $forwardToErpService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly StateMachineFactory $stateMachineFactory,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): Response
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        /** @var Generic|GetStatusInterface $status */
        $status = $this->getStatusFactory->createNewWithModel($token);
        $this->payum->getGateway($token->getGatewayName())->execute($status);

        $resolveNextRoute = $this->resolveNextRouteFactory->createNewWithModel($status->getFirstModel());
        $this->payum->getGateway($token->getGatewayName())->execute($resolveNextRoute);

        $this->getHttpRequestVerifier()->invalidate($token);
        if (PaymentInterface::STATE_COMPLETED === $status->getValue()) {
            $payment = $this->paymentRepository->findOneBy(['id' => $token->getDetails()->getId()]);
            $order = $this->orderRepository->findOneBy(['id' => $payment->getOrder()->getId()]);
            $checkoutCompletedAt = new \DateTime();
            $completeOrder = new CompleteOrder(
                $order->getTokenValue(),
                $this->provideUserEmail($order->getCustomer()->getEmail()),
                $checkoutCompletedAt,
                $request
            );
            $this->commandBus->handle($completeOrder);

            $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);
            if ($stateMachine->can(PaymentTransitions::TRANSITION_COMPLETE)) {
                $stateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);
            }

            $this->forwardToErpService->postComplete($order);
        }

        return new Response(
            $this->templating->render('@App/ShopApi/afterPay.html.twig', [
                'status' => $status->getValue(),
            ]),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    private function getHttpRequestVerifier(): HttpRequestVerifierInterface
    {
        return $this->payum->getHttpRequestVerifier();
    }

    private function provideUserEmail(string $email): string
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof ShopUserInterface) {
            return $user->getCustomer()->getEmail();
        }

        return $email;
    }
}
