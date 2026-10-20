<?php

namespace App\Services\Token;

interface CheckerInterface
{
    /**
     * @throws TokenException
     */
    public function check(string $token, ?string $email = null);
}
