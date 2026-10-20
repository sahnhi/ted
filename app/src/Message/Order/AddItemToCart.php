<?php

declare(strict_types=1);

namespace App\Message\Order;

use Sylius\Bundle\ApiBundle\Command\Cart\AddItemToCart as SyliusAddItemToCart;

final class AddItemToCart extends SyliusAddItemToCart
{
    use AddonsAwareTrait {
        __construct as initializeAddons;
    }

    public function __construct(
        string $productVariantCode,
        int $quantity,
        array $addons = []
    ) {
        parent::__construct($productVariantCode, $quantity);

        $this->initializeAddons($addons);
    }
}
