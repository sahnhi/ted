<?php

namespace App\View;

class AdjustmentView
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var PriceView
     */
    public $amount;

    public function __construct()
    {
        $this->amount = new PriceView();
    }
}
