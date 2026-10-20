<?php

declare(strict_types=1);

namespace App\Api\Odoo\Message;

final class Cancel implements OdooMessageInterface
{
    public function __construct(
        public readonly int $orderId
    ) {
    }
}
