<?php

declare(strict_types=1);

namespace App\Api\Odoo\Message\Order;

use App\Api\Odoo\Message\OdooMessageInterface;

final class DestockOrder implements OdooMessageInterface
{
    public function __construct(
        public readonly int $orderId
    ) {
    }
}
