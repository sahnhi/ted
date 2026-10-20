<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class MaintenanceController
{
    private bool $maintenance = false;

    public function __construct(string $maintenance)
    {
        $this->maintenance = 'true' === $maintenance;
    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse($this->maintenance);
    }
}
