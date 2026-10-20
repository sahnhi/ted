<?php

namespace App\Factory\View;

use App\Doctrine\Entity\OrderReason;
use App\View\OrderReasonTransmissionView;
use Sylius\Component\Core\Model\ChannelInterface;

class OrderReasonTransmissionViewFactory implements OrderReasonTransmissionViewFactoryInterface
{
    public function __construct(
        private readonly ProductVariantViewFactoryInterface $productVariantViewFactory
    ) {
    }

    public function createTransmission(OrderReason $reason, ChannelInterface $channel, string $locale): OrderReasonTransmissionView
    {
        $orderReasonTransmissionView = new OrderReasonTransmissionView();
        $orderReasonTransmissionView->name = $reason->getReason()->getName();
        $orderReasonTransmissionView->quantity = $reason->getQuantity();

        if (null !== $reason->getVariant()) {
            $orderReasonTransmissionView->variants[] = $this->productVariantViewFactory->createTransmission($reason->getVariant(), $channel, $locale);
        }

        return $orderReasonTransmissionView;
    }
}
