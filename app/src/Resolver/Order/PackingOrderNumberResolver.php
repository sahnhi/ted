<?php

declare(strict_types=1);

namespace App\Resolver\Order;

use App\Exception\Order\UnresolvableOrderNumberException;

class PackingOrderNumberResolver implements OrderNumberResolverInterface
{
    public const PACKING_DATA_ORDER_NUMBER_INDEX = 3;

    public function __construct(
        private readonly string $pattern
    ) {
    }

    /**
     * @throws UnresolvableOrderNumberException
     */
    public function resolve(array $context): string
    {
        $orderNumbers = array_column($context, self::PACKING_DATA_ORDER_NUMBER_INDEX);

        if (count(array_unique($orderNumbers)) > 1) {
            throw new UnresolvableOrderNumberException(sprintf('Too many order numbers in packing data: %s', implode(', ', $orderNumbers)));
        }

        $orderNumber = trim((string) $orderNumbers[0]);

        if (str_starts_with($orderNumber, $this->pattern)) {
            return substr($orderNumber, strlen($this->pattern));
        }

        throw new UnresolvableOrderNumberException(sprintf('Unable to resolve packing data for order number: %s', $orderNumber));
    }
}
