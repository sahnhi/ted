<?php

declare(strict_types=1);

namespace App\Api\Odoo\Message\Return;

use App\Api\Odoo\Message\OdooMessageInterface;

final class CreateReturn implements OdooMessageInterface
{
    public function __construct(
        public readonly int $orderId
    ) {
    }
}
