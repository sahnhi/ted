<?php

namespace App\ReorderProcessing;

use App\Doctrine\Entity\BundleAssociationInterface;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\OrderItem;
use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;

final class BundleProcessor implements ReorderProcessor
{
    public function __construct(
        private readonly OrderItemQuantityModifierInterface $quantityModifier
    ) {
    }

    /**
     * @param Order $reorder
     */
    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        foreach ($order->getItems() as $item) {
            /** @var ProductVariantInterface $variant */
            $variant = $item->getVariant();

            if ($variant->isBundle()) {
                $reorderItems = $reorder->getItemsByVariant($variant);

                if ($reorderItems->isEmpty()) {
                    continue;
                }

                $reorderItem = $reorderItems->first();
                $reorder->removeItem($reorderItem);
                $this->handleBundle($reorder, $variant, $reorderItem->getQuantity());
            }
        }
    }

    private function handleBundle(OrderInterface $reorder, ProductVariantInterface $bundle, int $bundleQty): void
    {
        /** @var BundleAssociationInterface $association */
        foreach ($bundle->getBundleAssociations() as $association) {
            $orderItem = new OrderItem();
            $orderItem->setVariant($association->getVariant());

            $reorder->addItem($orderItem);
            $this->quantityModifier->modify($orderItem, $association->getQuantity() * $bundleQty);
        }
    }
}
