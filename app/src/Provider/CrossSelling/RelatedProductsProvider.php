<?php

namespace App\Provider\CrossSelling;

use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Entity\Sylius\ProductAssociationTypeInterface;
use App\Doctrine\Entity\Sylius\ProductInterface;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

class RelatedProductsProvider
{
    public function provideForCart(OrderInterface $order): array
    {
        $relatedProducts = [];

        /** @var OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            /** @var ProductInterface|null $product */
            $product = $item->getProduct();
            if (null === $product) {
                continue;
            }

            /** @var Collection<Product>|null $associatedProducts */
            $associatedProducts = $product
                ->getAssociationByType(ProductAssociationTypeInterface::TYPE_CART_CROSS_SELLING)
                ?->getAssociatedProducts();

            if (null === $associatedProducts) {
                return [];
            }

            $relatedProducts = [...$relatedProducts, ...$associatedProducts->toArray()];
        }

        return $relatedProducts;
    }

    public function provideForProduct(ProductInterface $product): array
    {
        return $product
            ->getAssociationByType(ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING)
            ?->getAssociatedProducts()
            ->toArray() ?? [];
    }
}
