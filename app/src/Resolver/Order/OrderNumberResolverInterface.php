<?php

namespace App\Resolver\Order;

use App\Exception\Order\UnresolvableOrderNumberException;

interface OrderNumberResolverInterface
{
    /**
     * @throws UnresolvableOrderNumberException
     */
    public function resolve(array $context): string;
}
