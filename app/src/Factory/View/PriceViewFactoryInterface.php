<?php

namespace App\Factory\View;

use App\View\PriceView;

interface PriceViewFactoryInterface
{
    public function create(int $price, string $currency): PriceView;
}
