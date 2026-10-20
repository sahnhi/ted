<?php

namespace App\Services\OrderItem;

use App\Doctrine\Entity\Sylius\Order;

final class CreateOrderItemLogistic
{
    public function __construct(
        private readonly OrderItemService $orderItemService
    ) {
    }

    public function process(Order $order): void
    {
        $this->orderItemService->generateItemLogistic($order);
    }
}
