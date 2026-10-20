<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

use App\Processor\OrderMessage\Processors\Interfaces\ProductVariantCodeInOrderInterface;

final class BundleComboBedMonoblocProcessor extends AbstractOrderMessageProcessor implements ProductVariantCodeInOrderInterface
{
    public const ENTRY_NAME = 'bundle_combo_bed_monobloc';
}
