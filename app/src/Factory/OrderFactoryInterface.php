<?php

namespace App\Factory;

use App\Doctrine\Entity\Sylius\Order;
use Sylius\AdminOrderCreationPlugin\Factory\OrderFactoryInterface as BaseOrderFactoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface OrderFactoryInterface extends BaseOrderFactoryInterface
{
    public function createReorder(OrderInterface $order, string $channelCode, ?string $type = null): OrderInterface;

    public function createNewForChargesEstimate(array $variants, ChannelInterface $channel): Order;
}
