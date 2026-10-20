<?php

namespace App\Services\OutOfStock;

use App\Doctrine\Entity\Sylius\Order;

interface WeightingCalculationInterface
{
    public function recalculate(Order $order, ?Order $parent): int;
}
