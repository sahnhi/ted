<?php

declare(strict_types=1);

namespace App\Analytic\Command;

use Sylius\Component\Core\Model\OrderInterface;

final class ImportOrderCommand
{
    public function __construct(
        private readonly OrderInterface $order
    ) {
    }

    public function getOrder(): OrderInterface
    {
        return $this->order;
    }
}
