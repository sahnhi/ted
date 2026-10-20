<?php

namespace App\View;

class OrderReasonTransmissionView
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array|ProductVariantTransmissionView[]
     */
    public $variants = [];

    /**
     * @var string
     */
    public $quantity;
}
