<?php

namespace App\Factory\View;

use App\View\CartSummaryView;
use Sylius\Component\Core\Model\OrderInterface;

interface CartViewFactoryInterface
{
    public function create(OrderInterface $cart, string $localeCode): CartSummaryView;
}
