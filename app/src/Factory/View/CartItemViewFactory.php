<?php

namespace App\Factory\View;

use App\Doctrine\Entity\Sylius\OrderItem;
use App\View\ItemTransmissionView;
use App\View\ItemView;
use Sylius\Component\Core\Model\ChannelInterface;

final class CartItemViewFactory implements CartItemViewFactoryInterface
{
    public function __construct(
        private readonly ProductViewFactoryInterface $productViewFactory,
        private readonly ProductVariantViewFactoryInterface $productVariantViewFactory
    ) {
    }

    public function create(OrderItem $item, ChannelInterface $channel, string $locale, bool $withVariants = false): ItemView
    {
        $itemView = new ItemView();
        $itemView->id = $item->getId();
        $itemView->quantity = $item->getQuantity();
        $itemView->total = $item->getTotal();
        $itemView->totalWithoutPromotions = $item->getTotalWithoutAdjustments();
        $itemView->product = $this->productViewFactory->create($item->getProduct(), $channel, $locale, true, $withVariants);
        $itemView->product->variants = [$this->productVariantViewFactory->create($item->getVariant(), $channel, $locale)];

        return $itemView;
    }

    public function createTransmission(ChannelInterface $channel, string $locale, array $logisticItemArray): ItemTransmissionView
    {
        $itemTransmissionView = new ItemTransmissionView();

        $itemTransmissionView->quantity = $logisticItemArray['quantity'];
        $itemTransmissionView->total = $logisticItemArray['total'];
        $itemTransmissionView->itemCode = $logisticItemArray['itemCode'];
        $itemTransmissionView->productCode = $logisticItemArray['itemProduct']->getCode();
        $itemTransmissionView->parentProduct = $logisticItemArray['product'];

        $itemTransmissionView->product = $this->productViewFactory->createTransmission($logisticItemArray['itemProduct'], $channel, $locale);
        $itemTransmissionView->product->variants = [$this->productVariantViewFactory->createTransmission($logisticItemArray['variant'], $channel, $locale)];

        return $itemTransmissionView;
    }
}
