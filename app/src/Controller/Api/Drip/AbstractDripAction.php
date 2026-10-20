<?php

namespace App\Controller\Api\Drip;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class AbstractDripAction implements DripActionInterface
{
    public function __construct(
        private readonly string $token
    ) {
    }

    public function authenticate(?string $token): void
    {
        if ($token !== $this->token) {
            throw new AccessDeniedHttpException('Wrong token validation');
        }
    }
}
