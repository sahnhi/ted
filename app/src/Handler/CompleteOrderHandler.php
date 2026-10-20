<?php

namespace App\Handler;

use App\Business\Command\CompleteOrder;
use App\Doctrine\Entity\FrontData;
use App\Provider\CustomerProviderInterface;
use Doctrine\Persistence\ObjectManager;
use SM\Factory\Factory as StateMachineFactory;
use SM\SMException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class CompleteOrderHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ObjectManager $orderManager,
        private readonly CustomerProviderInterface $customerProvider,
        private readonly StateMachineFactory $stateMachineFactory,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * @throws SMException
     */
    public function handle(CompleteOrder $completeOrder): OrderInterface
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneBy(['tokenValue' => $completeOrder->getOrderToken()]);
        Assert::notNull(
            $order,
            $this->translator->trans('app.api.order.complete.error.not_found', ['token' => $completeOrder->getOrderToken()])
        );

        $stateMachine = $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH);
        Assert::true(
            $stateMachine->can(OrderCheckoutTransitions::TRANSITION_COMPLETE),
            $this->translator->trans('app.api.order.complete.error.cannot_complete', ['token' => $completeOrder->getOrderToken()])
        );

        $customer = $this->customerProvider->provide($completeOrder->getEmail());

        $order->setCustomer($customer);
        $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_COMPLETE);

        if ($completeOrder->hasFrontData()) {
            $frontData = new FrontData();
            $frontData->setFrontVersion($completeOrder->getBody()['version']);
            $frontData->setUserAgent($completeOrder->getBody()['user-agent']);
            $frontData->setState('completed');
            $frontData->setOrder($order);

            $order->addFrontData($frontData);
        }

        $this->orderManager->persist($order);
        $this->orderManager->flush();

        return $order;
    }
}
