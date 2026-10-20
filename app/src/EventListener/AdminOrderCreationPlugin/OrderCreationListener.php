<?php

namespace App\EventListener\AdminOrderCreationPlugin;

use App\Api\Odoo\Message\Order\DestockOrder;
use App\Api\Odoo\Message\Return\CreateReturn;
use App\Business\Command\ChooseShipping;
use App\Component\Order\Processor\CompositeReorderProcessor;
use App\Doctrine\Entity\Reason;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Shipment;
use App\Doctrine\Entity\Sylius\ShippingMethod;
use App\Services\Channel\ChannelCode;
use App\Services\OrderReturn\ReturnMailService;
use App\Services\ShippingMethod\ShippingMethodService;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

final class OrderCreationListener
{
    public const NO_PAYMENT_TRANSITION_NEEDED = [ChannelCode::CHANNEL_BIS, ChannelCode::CHANNEL_OPERATION, ChannelCode::CHANNEL_RETURN];

    public function __construct(
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly CompositeReorderProcessor $reorderProcessor,
        private readonly FactoryInterface $stateMachineFactory,
        private readonly RepositoryInterface $orderRepository,
        private readonly RequestStack $requestStack,
        private readonly ReturnMailService $returnMailService,
        private readonly ShippingMethodService $shippingMethodService,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function processOrderBeforeCreation(GenericEvent $event): void
    {
        /** @var Order $order */
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $order->recalculateAdjustmentsTotal();

        $this->orderProcessor->process($order);

        if (null !== $order->getParentNumber()) {
            $this->reorderProcess($order);
        }
    }

    /**
     * @throws SMException
     */
    public function completeOrderBeforeCreation(GenericEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        Assert::notNull($request, 'No current request found');

        /** @var Order $order */
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $stateMachine = $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH);
        $channel = $order->getChannel();
        Assert::notNull($channel);

        $toAddress = $request->get('toAddress') ?? true;
        $toComplete = $request->get('toComplete') ?? true;
        $toSelectShipping = $request->get('toSelectShipping') ?? true;
        $toSelectPayment = !in_array($channel->getCode(), self::NO_PAYMENT_TRANSITION_NEEDED);

        if (ChannelCode::CHANNEL_B2B === $channel->getCode()) {
            $toSelectPayment = $order->getTotal() > 0;
        }

        if ($toAddress) {
            $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_ADDRESS);
        }

        if ($toSelectShipping && $stateMachine->can(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING)) {
            $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING);
        }

        if ($toSelectPayment && $stateMachine->can(OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT)) {
            $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT);
        }

        if ($toComplete && $stateMachine->can(OrderCheckoutTransitions::TRANSITION_COMPLETE)) {
            $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_COMPLETE);
        }

        if (ChannelCode::CHANNEL_RETURN === $channel->getCode() && Reason::RETURN_TYPE_AUTONOMOUS !== $order->getReturnType()) {
            $this->returnMailService->process($order);
        }
    }

    private function reorderProcess(Order $order): void
    {
        $request = $this->requestStack->getCurrentRequest();
        Assert::notNull($request, 'No current request found');

        $parent = $this->orderRepository->findOneBy(['number' => $order->getParentNumber()]);
        Assert::notNull($parent);

        $form = $request->get('sylius_admin_order_creation_new_order');
        $returnType = $form['returnType'] ?? null;
        $bisType = $request->get('bisType');

        Assert::notNull($order->getChannel());
        $this->reorderProcessor->process($parent, $order, $order->getChannel()->getCode());

        if (null !== $returnType && ChannelCode::CHANNEL_RETURN === $order->getChannel()->getCode()) {
            $this->reorderProcessor->process($parent, $order, $returnType);
        }

        if (null !== $bisType && ChannelCode::CHANNEL_BIS === $order->getChannel()->getCode()) {
            $this->reorderProcessor->process($parent, $order, $bisType);
        }
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Order) {
            return;
        }

        $orderChannelCode = $entity->getChannel()->getCode();
        switch ($orderChannelCode) {
            case ChannelCode::CHANNEL_RETURN:
                if (Reason::RETURN_TYPE_AUTONOMOUS === $entity->getReturnType()) {
                    // We send the return to Odoo only when the customer picked his calendar slot
                    return;
                }
                $message = new CreateReturn(orderId: $entity->getId());
                break;
            case ChannelCode::CHANNEL_BIS:
                $message = new DestockOrder(orderId: $entity->getId());
                break;
            default:
                return;
        }

        $this->messageBus->dispatch($message);
    }

    /**
     * @throws \Exception
     */
    public function checkShippingMethod(GenericEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        Assert::notNull($request, 'No current request found');

        /** @var Order $order */
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        /** @var Shipment|null $shipment */
        $shipment = $order->getShipments()->first();

        $requestData = $request->request->get('sylius_admin_order_creation_new_order', []);

        if (null !== $shipment && isset($requestData['shipments'])) {
            /** @var ShippingMethod $shippingMethod */
            $shippingMethod = $shipment->getMethod();

            $chooseShipping = new ChooseShipping();
            $chooseShipping->createFromAdminRequest($request, $order);

            $this->shippingMethodService->chooseShippingMethod(
                $order,
                $shipment,
                $shippingMethod,
                $chooseShipping
            );
        }
    }
}
