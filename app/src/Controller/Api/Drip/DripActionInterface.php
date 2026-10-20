<?php

namespace App\Controller\Api\Drip;

interface DripActionInterface
{
    public function authenticate(?string $token): void;
}
