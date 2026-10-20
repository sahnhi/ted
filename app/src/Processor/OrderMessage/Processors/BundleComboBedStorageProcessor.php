<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

use App\Processor\OrderMessage\Processors\Interfaces\ProductVariantCodeInOrderStartsWithInterface;

final class BundleComboBedStorageProcessor extends AbstractOrderMessageProcessor implements ProductVariantCodeInOrderStartsWithInterface
{
    public const ENTRY_NAME = 'bed_combo_bed_storage';
}
