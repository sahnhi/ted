<?php

namespace App\Modifier;

use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;

class OrderModifier implements OrderModifierInterface
{
    public function __construct(
        private readonly CartItemFactoryInterface $cartItemFactory,
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly ObjectManager $orderManager
    ) {
    }

    public function modify(OrderInterface $order, ProductVariantInterface $productVariant, int $quantity): void
    {
        $cartItem = $this->getCartItemToModify($order, $productVariant);
        if (null !== $cartItem) {
            $this->orderItemQuantityModifier->modify($cartItem, $cartItem->getQuantity() + $quantity);
            $this->orderProcessor->process($order);

            return;
        }

        $cartItem = $this->cartItemFactory->createForCart($order);
        $cartItem->setVariant($productVariant);
        $this->orderItemQuantityModifier->modify($cartItem, $quantity);
        $order->addItem($cartItem);
        $this->orderProcessor->process($order);
        $this->orderManager->persist($order);
    }

    private function getCartItemToModify(OrderInterface $cart, ProductVariantInterface $productVariant): ?OrderItemInterface
    {
        /** @var OrderItemInterface $cartItem */
        foreach ($cart->getItems() as $cartItem) {
            if ($productVariant === $cartItem->getVariant()) {
                return $cartItem;
            }
        }

        return null;
    }
}
