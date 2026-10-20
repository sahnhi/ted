<?php

namespace App\Business\Command;

final class ChoosePayment
{
    public function __construct(
        private readonly string $token,
        private readonly string $code
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
