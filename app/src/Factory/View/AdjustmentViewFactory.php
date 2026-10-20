<?php

namespace App\Factory\View;

use App\View\AdjustmentView;
use Sylius\Component\Core\Model\AdjustmentInterface;

final class AdjustmentViewFactory implements AdjustmentViewFactoryInterface
{
    public function __construct(
        private readonly PriceViewFactoryInterface $priceViewFactory
    ) {
    }

    public function create(AdjustmentInterface $adjustment, int $additionalAmount, string $currency): AdjustmentView
    {
        $adjustmentView = new AdjustmentView();
        $adjustmentView->name = $adjustment->getLabel();
        $adjustmentView->amount = $this->priceViewFactory->create($adjustment->getAmount() + $additionalAmount, $currency);

        return $adjustmentView;
    }
}
