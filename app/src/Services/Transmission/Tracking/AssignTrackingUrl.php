<?php

namespace App\Services\Transmission\Tracking;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;
use App\Exception\OrderNotFound;
use App\Services\Sentry\ReportingInterface;
use Sentry;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AssignTrackingUrl
{
    public function __construct(
        private readonly RepositoryInterface $orderRepository,
        private readonly TrackingService $trackingService,
        private readonly ReportingInterface $reporting
    ) {
    }

    public function process(string $tokenValue, $trackingUrl): void
    {
        try {
            /** @var Order $order */
            $order = $this->orderRepository->getOrderForTrackingEmailByToken($tokenValue);
            if (!$order instanceof Order) {
                throw new OrderNotFound('Order with given tokenValue does not exists');
            }

            if (OrderShippingStates::STATE_READY !== $order->getShippingState()) {
                return;
            }

            $url = is_array($trackingUrl) && !array_key_exists('link', $trackingUrl) ? $trackingUrl : [$trackingUrl];

            if (!empty($order->getShipments()) && str_contains((string) $order->getShipments()->first()->getMethod()->getCode(), ShippingMethodInterface::METHOD_VIR)) {
                $url = null;
            }

            $this->trackingService->setTracking($url, $order);
        } catch (\Exception $e) {
            $orderNumber = null !== $order ? $order->getNumber() : $tokenValue;
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::error(),
                [
                    'Action' => 'Tracking',
                    'order_number' => $orderNumber,
                ]
            );
        }
    }
}
