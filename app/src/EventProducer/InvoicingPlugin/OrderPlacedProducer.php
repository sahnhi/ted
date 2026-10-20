<?php

namespace App\EventProducer\InvoicingPlugin;

use App\Component\Core\OrderShippingStates;
use App\Generator\Renderer\SwissInvoiceRenderer;
use App\Services\Channel\ChannelCode;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\InvoicingPlugin\Event\OrderPlaced;
use Symfony\Component\Messenger\MessageBusInterface;

final class OrderPlacedProducer
{
    public function __construct(
        private readonly MessageBusInterface $eventBus
    ) {
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $order = $event->getObject();

        if (!$order instanceof OrderInterface
            || OrderPaymentStates::STATE_PAID !== $order->getPaymentState()
        ) {
            return;
        }

        $this->dispatchOrderPlacedEvent($order);
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $order = $event->getObject();

        if (!$order instanceof OrderInterface
            || OrderPaymentStates::STATE_PAID !== $order->getPaymentState()
        ) {
            return;
        }

        $this->dispatchOrderPlacedEvent($order);
    }

    private function dispatchOrderPlacedEvent(OrderInterface $order): void
    {
        $channelCode = $order->getChannel()->getCode();

        if (!in_array($channelCode, [ChannelCode::CHANNEL_B2B, ChannelCode::CHANNEL_RETURN, ChannelCode::CHANNEL_OPERATION], true)) {
            if (ChannelCode::CHANNEL_BIS === $channelCode && SwissInvoiceRenderer::SWISS_TAX_CODE !== $order->getShippingAddress()->getCountryCode() || OrderShippingStates::STATE_SHIPPED !== $order->getShippingState()) {
                return;
            }

            $this->eventBus->dispatch(new OrderPlaced($order->getNumber(), new \DateTimeImmutable('now')));
        }
    }
}
