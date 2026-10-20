<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors;

final class PackOnCartProcessor extends AbstractOrderMessageProcessor
{
    public const ENTRY_NAME = 'pack_on_cart';
}
