<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

use App\Processor\OrderMessage\Processors\Interfaces\ProductVariantCodeInOrderStartsWithInterface;

final class BedFrameTooLargeProcessor extends AbstractOrderMessageProcessor implements ProductVariantCodeInOrderStartsWithInterface
{
    public const ENTRY_NAME = 'bed_frame_too_large';
}
