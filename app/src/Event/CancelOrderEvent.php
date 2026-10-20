<?php

declare(strict_types=1);

namespace App\Event;

use App\Doctrine\Entity\Sylius\Order;
use Symfony\Contracts\EventDispatcher\Event;

final class CancelOrderEvent extends Event
{
    public const NAME = 'order.cancel';

    public function __construct(
        private readonly Order $order
    ) {
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
