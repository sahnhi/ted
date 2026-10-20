<?php

namespace App\Modifier;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface OrderModifierInterface
{
    public function modify(OrderInterface $order, ProductVariantInterface $productVariant, int $quantity): void;
}
