<?php

namespace App\Model\BalouImport;

use App\Doctrine\Entity\Sylius\ProductVariantInterface;
use App\Doctrine\Entity\Sylius\ShippingMethodInterface;

class BalouOrderItem
{
    /**
     * @var BalouOrder
     */
    public $order;

    /**
     * @var ProductVariantInterface
     */
    public $variant;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var ShippingMethodInterface
     */
    public $shippingMethod;

    /**
     * @var \DateTime
     */
    public $shippingDate;

    /**
     * @var string
     */
    public $code;
}
