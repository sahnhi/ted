<?php

namespace App\ViewRepository;

use App\Factory\View\OrderTransmissionViewFactory;
use App\View\OrderTransmissionView;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Webmozart\Assert\Assert;

final class OrderTransmissionViewRepository implements OrderTransmissionViewRepositoryInterface
{
    /**
     * OrderTransmissionViewRepository constructor.
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderTransmissionViewFactory $orderTransmissionViewFactory
    ) {
    }

    public function getOneById(string $orderId): OrderTransmissionView
    {
        $order = $this->orderRepository->find($orderId);
        Assert::notNull($order, 'Order with given number does not exists');

        return $this->orderTransmissionViewFactory->create($order, $order->getLocaleCode());
    }
}
