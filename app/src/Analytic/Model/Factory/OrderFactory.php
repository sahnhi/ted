<?php

declare(strict_types=1);

namespace App\Analytic\Model\Factory;

use App\Analytic\Model\Order;
use App\Analytic\Model\OrderItem;
use App\Analytic\Model\OrderItemList;
use App\Doctrine\Entity\Sylius\Order as SyliusOrder;
use App\Doctrine\Entity\Sylius\OrderItemUnit;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Exception\OrderNotFound;
use App\Services\Channel\ChannelCode;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\AdjustmentInterface;
use Sylius\Component\Order\Model\OrderInterface as OrderInterfaceAlias;

final class OrderFactory
{
    private const DEFAULT_DOMAIN_STORE = 'stecroix';
    private const WEBSITE_DOMAIN = 'website';
    private const RETURN_DOMAIN = 'retour';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    public function create(OrderInterface $order): Order
    {
        $original = $this->orderRepository->findInitialOrder($order->getNumber());
        if (null === $original) {
            throw new OrderNotFound();
        }

        /** @var SyliusOrder $order */
        $channel = $order->getChannel();
        if (null === $channel) {
            throw new ChannelNotFoundException();
        }

        /** @var SyliusOrder $original */
        $orderReason = $original->getOrderReason();
        $cancelReason = null;
        if (OrderInterfaceAlias::STATE_CANCELLED === $original->getState() && !$orderReason->isEmpty() && null !== $orderReason->first()->getReason()) {
            $cancelReason = $orderReason->first()->getReason()->getName();
        }

        $linkedOrders = $this->orderRepository->findAssociatedOrders($original->getNumber());

        $orderItems = OrderItemList::empty();
        foreach ($linkedOrders as $orderArray) {
            foreach ($orderArray as $orderRow) {
                /** @var \App\Doctrine\Entity\Sylius\OrderItem $item */
                foreach ($orderRow->getItems() as $item) {
                    $taxes = 0;
                    /** @var OrderItemUnit $unit */
                    foreach ($item->getUnits() as $unit) {
                        /** @var AdjustmentInterface $adjustment */
                        foreach ($unit->getAdjustments('tax') as $adjustment) {
                            $taxes += $adjustment->getAmount();
                        }
                    }

                    $orderItem = new OrderItem(
                        (string) $item->getId(),
                        $item->getProduct()->getName(),
                        $item->getVariant()->getName(),
                        $item->getTotal() - $taxes,
                        $item->getQuantity(),
                        null === $item->getProduct()->getMainTaxon() ? $item->getProduct()->getCode() : $item->getProduct()->getMainTaxon()->getCode(),
                        $item->getVariant()->getCode()
                    );

                    $orderItems = $orderItems->addToList($orderItem);
                }
            }
        }

        return new Order(
            (string) $original->getId(),
            $original->getCustomer()->getCreatedAt(),
            $original->getCustomer()->getCreatedAt(),
            $original->getNumber(),
            $original->getCreatedAt(),
            $this->getDomain($order, $channel->getCode()),
            $original->getTotal() - $original->getTaxTotal(),
            $original->getTotal(),
            $order->getUpdatedAt(),
            $cancelReason,
            $orderItems
        );
    }

    private function getDomain(SyliusOrder $order, string $channelCode): string
    {
        switch ($channelCode) {
            case ChannelCode::CHANNEL_RETAIL:
                $orderStore = $order->getOrderStore();
                if (null !== $orderStore && null !== $orderStore->getStore() && null !== $orderStore->getStore()->getName()) {
                    return strtolower(str_replace('_', '', trim($orderStore->getStore()->getName())));
                }

                return self::DEFAULT_DOMAIN_STORE;
            case ChannelCode::CHANNEL_RETURN:
                return self::RETURN_DOMAIN;
            case ChannelCode::CHANNEL_WEB:
                return self::WEBSITE_DOMAIN;
            default:
                return $channelCode;
        }
    }
}
