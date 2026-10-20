<?php

declare(strict_types=1);

namespace App\Event;

use App\Doctrine\Entity\OrderStore;
use Symfony\Contracts\EventDispatcher\Event;

final class UpdateOrderStoreEvent extends Event
{
    public const NAME = 'order.store.updated';

    public function __construct(
        private readonly OrderStore $orderStore,
        private readonly ?string $basicOrderId = null
    ) {
    }

    public function getOrderStore(): OrderStore
    {
        return $this->orderStore;
    }

    public function getBasicOrderId(): ?string
    {
        return $this->basicOrderId;
    }
}
