<?php

declare(strict_types=1);

namespace App\Resolver\Order;

use App\Exception\Order\UnresolvableOrderNumberException;

class DefaultPackingOrderNumberResolver implements OrderNumberResolverInterface
{
    public const PACKING_DATA_ORDER_NUMBER_INDEX = 3;

    public function resolve(array $context): string
    {
        $orderNumbers = array_column($context, self::PACKING_DATA_ORDER_NUMBER_INDEX);

        if (count(array_unique($orderNumbers)) > 1) {
            throw new UnresolvableOrderNumberException(sprintf('Too many order numbers in packing data: %s', implode(', ', $orderNumbers)));
        }

        $orderNumber = trim((string) $orderNumbers[0]);

        $matches = [];
        preg_match('/^([A-Z]*)(?<orderNumber>\d+)$/', $orderNumber, $matches);

        return $matches['orderNumber']
            ?? throw new UnresolvableOrderNumberException(sprintf('Unable to resolve packing data for order number: %s', $orderNumber));
    }
}
