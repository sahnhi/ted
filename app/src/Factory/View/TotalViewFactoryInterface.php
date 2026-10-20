<?php

namespace App\Factory\View;

use App\View\TotalsView;
use Sylius\Component\Core\Model\OrderInterface;

interface TotalViewFactoryInterface
{
    public function create(OrderInterface $cart): TotalsView;
}
