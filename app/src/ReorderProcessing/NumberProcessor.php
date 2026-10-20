<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Services\Channel\ChannelCode;
use App\Services\OrderReturn\ReturnTypeServiceInterface;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;
use Webmozart\Assert\Assert;

final class NumberProcessor implements ReorderProcessor
{
    public const BIS_PREFIX = 'B';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ReturnTypeServiceInterface $returnTypeService
    ) {
    }

    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        $channel = $reorder->getChannel();
        Assert::notNull($channel, 'Channel cannot be null');

        switch ($channel->getCode()) {
            case ChannelCode::CHANNEL_BIS:
                $this->handleBisNumber($order, $reorder);
                break;

            case ChannelCode::CHANNEL_RETURN:
                $this->handleReturnNumber($order, $reorder);
                break;
        }
    }

    private function handleBisNumber(Order $order, Order $reorder)
    {
        $bis = $this->orderRepository->getBisNumber($order->getParentNumber() ?? $order->getNumber());
        $number = $order->getParentNumber() ?? $order->getNumber();
        ++$bis;

        $reorder->setBis($bis);
        $reorder->setParentNumber($number);
        $reorder->setNumber($number . self::BIS_PREFIX . $bis);
    }

    private function handleReturnNumber(Order $order, Order $reorder)
    {
        $return = $this->orderRepository->getReturnNumber($order->getNumber());
        ++$return;

        $reorder->setReturn($return);
        $reorder->setParentNumber($order->getNumber());

        $prefix = $this->returnTypeService->getReturnOrderPrefix($reorder);
        Assert::notNull($prefix, 'Return Type cannot be null');

        $reorder->setNumber($order->getNumber() . $prefix . $return);
    }
}
