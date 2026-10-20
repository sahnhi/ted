<?php

namespace App\Factory\View;

use App\View\AdjustmentView;
use Sylius\Component\Core\Model\AdjustmentInterface;

interface AdjustmentViewFactoryInterface
{
    public function create(AdjustmentInterface $adjustment, int $additionalAmount, string $currency): AdjustmentView;
}
