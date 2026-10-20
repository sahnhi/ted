<?php

namespace App\Message;

use Payum\Core\Bridge\Spl\ArrayObject;

class DalenysNotify
{
    public function __construct(
        private readonly int $paymentId,
        private readonly ArrayObject $details,
        private readonly array $payload
    ) {
    }

    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    public function getDetails(): ArrayObject
    {
        return $this->details;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
