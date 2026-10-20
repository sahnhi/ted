<?php

namespace App\View;

class ItemView
{
    public $id;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var int
     */
    public $total;

    /**
     * @var ProductView
     */
    public $product;

    public int $totalWithoutPromotions;

    public function __construct()
    {
        $this->product = new ProductView();
    }
}
