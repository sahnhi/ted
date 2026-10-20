<?php

declare(strict_types=1);

namespace App\Services\Payment\Model;

final class PaymentDetails implements \JsonSerializable
{
    public function __construct(
        private readonly string $pspReference,
        private readonly string $reason
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
          'pspReference' => $this->pspReference,
          'resultCode' => $this->reason,
        ];
    }
}
