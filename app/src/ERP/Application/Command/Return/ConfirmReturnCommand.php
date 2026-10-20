<?php

declare(strict_types=1);

namespace App\ERP\Application\Command\Return;

use App\ERP\Infrastructure\Shared\Bus\Command\CommandInterface;

final class ConfirmReturnCommand implements CommandInterface
{
    public function __construct(
        public int $orderId,
    ) {
    }
}
