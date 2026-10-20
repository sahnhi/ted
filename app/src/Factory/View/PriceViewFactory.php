<?php

namespace App\Factory\View;

use App\View\PriceView;

final class PriceViewFactory implements PriceViewFactoryInterface
{
    public function create(int $price, string $currency): PriceView
    {
        $priceView = new PriceView();
        $priceView->current = $price;
        $priceView->currency = $currency;

        return $priceView;
    }
}
