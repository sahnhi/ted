<?php

declare(strict_types=1);

namespace App\Analytic\Command;

use App\Doctrine\Entity\Sylius\OrderInterface;

final class ImportUserFromOrder
{
    /** use full order because at stage address in the order state machine, order is not flush yet in database */
    public function __construct(
        private readonly OrderInterface $order
    ) {
    }

    public function getOrder(): OrderInterface
    {
        return $this->order;
    }
}
