<?php

namespace App\View;

class ShippingMethodView
{
    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var PriceView
     */
    public $price;

    public function __construct()
    {
        $this->price = new PriceView();
    }
}
