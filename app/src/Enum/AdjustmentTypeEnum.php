<?php

declare(strict_types=1);

namespace App\Enum;

enum AdjustmentTypeEnum: string
{
    case OFFERED_SERVICE = 'offered_service';
    case SHIPPING_METHOD_SERVICE = 'shipping_method_service';

    public function getLabel(): string
    {
        return sprintf('app.adjustment.types.%s', $this->value);
    }
}
