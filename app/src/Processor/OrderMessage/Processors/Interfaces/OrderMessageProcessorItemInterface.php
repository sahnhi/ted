<?php

declare(strict_types=1);

namespace App\Processor\OrderMessage\Processors\Interfaces;

use App\Doctrine\Entity\Sylius\OrderInterface;

interface OrderMessageProcessorItemInterface
{
    public function process(OrderInterface $order): void;
}
