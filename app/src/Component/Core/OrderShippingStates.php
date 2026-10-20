<?php

declare(strict_types=1);

namespace App\Component\Core;

final class OrderShippingStates
{
    public const STATE_CART = 'cart';

    public const STATE_READY = 'ready';

    public const STATE_CANCELLED = 'cancelled';

    public const STATE_PARTIALLY_SHIPPED = 'partially_shipped';

    public const STATE_SHIPPED = 'shipped';

    public const STATE_DELIVERED = 'delivered';

    private function __construct()
    {
    }
}
