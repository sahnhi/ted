<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

use App\Processor\OrderMessage\Processors\Interfaces\ProductVariantCodeInOrderInterface;

final class BundleBedFrameTooLargeProcessor extends AbstractOrderMessageProcessor implements ProductVariantCodeInOrderInterface
{
    public const ENTRY_NAME = 'bundle_bed_frame_too_large';
}
