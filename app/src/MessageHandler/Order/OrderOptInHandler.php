<?php

declare(strict_types=1);

namespace App\MessageHandler\Order;

use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Exception\OrderNotFound;
use App\Model\Order\OrderOptIn;
use Doctrine\ORM\EntityManagerInterface;
use SM\Factory\FactoryInterface;
use Sylius\Bundle\ApiBundle\Provider\CustomerProviderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class OrderOptInHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CustomerProviderInterface $customerProvider,
        private readonly EntityManagerInterface $entityManager,
        private readonly FactoryInterface $stateMachineFactory,
    ) {
    }

    public function __invoke(OrderOptIn $message): Order
    {
        /** @var Order|null $order */
        $order = $this->orderRepository->findOneBy(['tokenValue' => $message->getOrderTokenValue()]);

        if (null === $order) {
            throw new OrderNotFound(sprintf('Order with token value "%s" not found.', $message->getOrderTokenValue()));
        }

        /** @var Customer|null $customer */
        $customer = $order->getCustomer();
        if (null === $customer) {
            /** @var Customer $customer */
            $customer = $this->customerProvider->provide($message->email);

            $this->entityManager->persist($customer);

            $order->setCustomer($customer);
        }

        $customer->setOptin(true);
        $customer->setSubscribedToNewsletter(true);

        // Apparently, needed for Drip
        $stateMachine = $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH);
        if ($stateMachine->can(OrderCheckoutTransitions::TRANSITION_ADDRESS)) {
            $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_ADDRESS);
        }

        return $order;
    }
}
