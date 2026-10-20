<?php

declare(strict_types=1);

namespace App\ERP\Application\Command\Order;

use App\ERP\Infrastructure\Shared\Bus\Command\CommandInterface;

final class CreateOrderCommand implements CommandInterface
{
    public function __construct(
        public int $orderId,
    ) {
    }
}
