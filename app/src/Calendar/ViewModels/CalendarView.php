<?php

declare(strict_types=1);

namespace App\Calendar\ViewModels;

use App\View\CartSummaryView;

final class CalendarView
{
    public function __construct(
        private readonly CalendarSlots $slots,
        private readonly CartSummaryView $order
    ) {
    }

    public function getSlots(): CalendarSlots
    {
        return $this->slots;
    }

    public function getOrder(): CartSummaryView
    {
        return $this->order;
    }
}
