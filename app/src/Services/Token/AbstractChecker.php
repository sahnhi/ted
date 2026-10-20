<?php

namespace App\Services\Token;

abstract class AbstractChecker implements CheckerInterface
{
    /**
     * @throws TokenException
     */
    abstract public function check(string $token, ?string $email = null);
}
