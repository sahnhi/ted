<?php

declare(strict_types=1);

namespace App\Exception;

final class ProductVariantNotFound extends \InvalidArgumentException
{
    public function __construct(string $code)
    {
        parent::__construct(sprintf('product variant not found with code %s', $code));
    }
}
