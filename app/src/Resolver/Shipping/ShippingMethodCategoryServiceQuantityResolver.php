<?php

declare(strict_types=1);

namespace App\Resolver\Shipping;

use App\Doctrine\Entity\ShippingMethodCategoryService;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductVariant;

class ShippingMethodCategoryServiceQuantityResolver
{
    public function resolveServiceQuantity(ShippingMethodCategoryService $service, Order $order): int
    {
        $items = $order->getItems();
        if ($items->isEmpty()) {
            return 0;
        }

        $restrictedProducts = $service->getRestrictedProducts();

        // If the given service is NOT restricted to any products, AND we can't determine which orderItem allows us to
        // add this service, we consider that this service can be added one times.
        if ($restrictedProducts->isEmpty()) {
            return 1;
        }

        $quantity = $bundleQuantity = 0;

        foreach ($items as $item) {
            /** @var ProductVariant $variant */
            $variant = $item->getVariant();

            if ($variant->isBundle()) {
                foreach ($variant->getBundleAssociations() as $bundleAssociation) {
                    /** @var Product|null $bundleProduct */
                    $bundleProduct = $bundleAssociation->getVariant()?->getProduct();
                    if (null === $bundleProduct) {
                        continue;
                    }

                    if ($restrictedProducts->contains($bundleProduct)) {
                        $bundleQuantity += $bundleAssociation->getQuantity() * $item->getQuantity();
                    }
                }

                continue;
            }

            if ($restrictedProducts->contains($variant->getProduct())) {
                $quantity += $item->getQuantity();
            }
        }

        return $bundleQuantity + $quantity;
    }
}
