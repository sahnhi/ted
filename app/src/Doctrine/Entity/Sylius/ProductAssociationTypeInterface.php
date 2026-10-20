<?php

namespace App\Doctrine\Entity\Sylius;

use Sylius\Component\Product\Model\ProductAssociationTypeInterface as BaseProductAssociationTypeInterfaceAlias;

interface ProductAssociationTypeInterface extends BaseProductAssociationTypeInterfaceAlias
{
    public const TYPE_CART_CROSS_SELLING = 'cart_cross_selling';
    public const TYPE_PRODUCT_CROSS_SELLING = 'product_cross_selling';
}
