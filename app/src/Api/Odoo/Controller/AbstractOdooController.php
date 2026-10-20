<?php

namespace App\Api\Odoo\Controller;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\InvalidArgumentException;

abstract class AbstractOdooController
{
    public function __construct(
        protected LoggerInterface $odooLogger,
        protected string $apiKey
    ) {
    }

    public function authenticate(Request $request): void
    {
        $apiKey = $request->headers->get('api-key');

        if ($this->apiKey !== $apiKey) {
            throw new InvalidArgumentException('api key error', 400);
        }
    }

    public function logRequest(Request $request): void
    {
        $this->odooLogger->log(LogLevel::INFO, 'New Odoo -> Sylius api call', [
            'route' => $request->getRequestUri(),
            'payload' => json_encode($request->request->all()),
        ]);
    }

    public function logError(Request $request, int $code, string $message): void
    {
        $this->odooLogger->log(LogLevel::ERROR, 'Odoo -> Sylius call failed', [
            'error' => $message,
            'code' => $code,
            'route' => $request->getRequestUri(),
            'payload' => json_encode($request->request->all()),
        ]);
    }
}
