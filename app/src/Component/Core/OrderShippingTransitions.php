<?php

declare(strict_types=1);

namespace App\Component\Core;

final class OrderShippingTransitions
{
    public const GRAPH = 'sylius_order_shipping';

    public const TRANSITION_REQUEST_SHIPPING = 'request_shipping';

    public const TRANSITION_PARTIALLY_SHIP = 'partially_ship';

    public const TRANSITION_SHIP = 'ship';

    public const TRANSITION_DELIVER = 'deliver';

    public const TRANSITION_CANCEL = 'cancel';

    private function __construct()
    {
    }
}
