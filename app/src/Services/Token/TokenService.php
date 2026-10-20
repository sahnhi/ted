<?php

namespace App\Services\Token;

use App\Services\Token\Checker\NotFoundException;

class TokenService
{
    public function __construct(
        private readonly CheckerFactory $checkerFactory
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws TokenException
     */
    public function checkToken(string $type, string $token, ?string $email = null)
    {
        $checker = $this->checkerFactory->get($type);
        $checker->check($token, $email);
    }
}
