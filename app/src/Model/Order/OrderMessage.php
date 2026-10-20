<?php

declare(strict_types=1);

namespace App\Model\Order;

class OrderMessage implements OrderMessageInterface
{
    public function __construct(
        private readonly string $message,
        private readonly string $type
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
