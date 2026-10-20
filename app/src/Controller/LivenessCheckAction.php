<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

final class LivenessCheckAction
{
    public function __invoke(): Response
    {
        return new Response();
    }
}
