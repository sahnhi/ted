<?php

declare(strict_types=1);

namespace App\EventListener\Order;

use App\Doctrine\Entity\Sylius\Order;
use App\Services\Sentry\Reporting;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Sentry\Severity;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;

final class PreUpdateListener
{
    public function __construct(
        private readonly Reporting $reporting
    ) {
    }

    public function __invoke(PreUpdateEventArgs $event): void
    {
        /** @var Order $order */
        $order = $event->getObject();

        if (!$order instanceof Order) {
            return;
        }

        if (OrderCheckoutStates::STATE_COMPLETED === $order->getCheckoutState()
            && empty($order->getCustomer()->getFirstName())
            && empty($order->getCustomer()->getLastName())
        ) {
            $this->reporting->reportIssue(
                'Customer has no firstname and lastname',
                Severity::info(),
                [
                    'order' => $order->getNumber(),
                    'shipping_address_id' => $order->getShippingAddress()->getId(),
                    'billing_address_id' => $order->getBillingAddress()->getId(),
                ]
            );
        }

        $changeSet = $event->getEntityChangeSet();

        if (!array_key_exists('itemsTotal', $changeSet) && !array_key_exists('total', $changeSet)) {
            return;
        }

        if (OrderPaymentStates::STATE_CART !== $order->getPaymentState()) {
            $this->reporting->reportIssue(
                'order total modification',
                Severity::error(),
                [
                    'orderId' => $order->getId(),
                    'orderNumber' => $order->getNumber(),
                ],
                [
                    'changeSet' => $changeSet,
                ]
            );
        }
    }
}
