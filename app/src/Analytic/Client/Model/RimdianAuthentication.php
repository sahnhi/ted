<?php

declare(strict_types=1);

namespace App\Analytic\Client\Model;

final class RimdianAuthentication
{
    public function __construct(
        private readonly string $refreshToken,
        private readonly string $accessToken
    ) {
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
