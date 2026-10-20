<?php

declare(strict_types=1);

namespace App\Exception;

class OutOfStockException extends \DomainException implements \Throwable
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
