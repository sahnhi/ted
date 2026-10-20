<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Exception;

final class OrderCannotWeightZeroGram extends \InvalidArgumentException
{
    public function __construct(?string $tokenOrder = null)
    {
        if (null !== $tokenOrder) {
            parent::__construct(sprintf('The weight of the order is 0 for order with token %s', $tokenOrder));
        } else {
            parent::__construct('The weight of the order is 0');
        }
    }
}
