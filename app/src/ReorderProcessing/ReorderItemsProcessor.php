<?php

declare(strict_types=1);

namespace App\ReorderProcessing;

use App\Doctrine\Entity\Sylius\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\AdminOrderCreationPlugin\ReorderProcessing\ReorderProcessor;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ReorderItemsProcessor implements ReorderProcessor
{
    public function __construct(
        private readonly FactoryInterface $orderItemFactory,
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        foreach ($order->getItems() as $item) {
            $hasItem = false;
            foreach ($reorder->getItems() as $reorderItem) {
                if ($reorderItem->getVariant() === $item->getVariant()) {
                    $hasItem = true;
                }
            }

            if (!$hasItem) {
                /** @var OrderItem $newItem */
                $newItem = $this->orderItemFactory->createNew();
                $newItem->setVariant($item->getVariant());
                $newItem->setUnitPrice(0);

                $this->entityManager->persist($newItem);

                $this->orderItemQuantityModifier->modify($newItem, $item->getQuantity());
                $reorder->addItem($newItem);
            }
        }
    }
}
