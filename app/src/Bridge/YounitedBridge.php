<?php

namespace App\Bridge;

use App\Legacy\Younited\YounitedApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class YounitedBridge implements YounitedBridgeInterface
{
    private string $clientId;
    private string $clientSecret;
    private string $resource;
    private string $environment;

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId)
    {
        $this->clientId = $clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function setResource(string $resource)
    {
        $this->resource = $resource;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment)
    {
        $this->environment = $environment;
    }

    public function isPostMethod(): bool
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        return 'POST' === $currentRequest->getMethod();
    }

    public function getDecisionResult()
    {
        if ($this->isPostMethod()) {
            $content = json_decode((string) $this->getContent(), true);

            return $content['DecisionResult'];
        }

        return false;
    }

    public function getReasonCode()
    {
        if ($this->isPostMethod()) {
            $content = json_decode((string) $this->getContent(), true);

            return $content['ReasonCode'];
        }

        return false;
    }

    public function createYounited($clientId, $clientSecret, $resource): YounitedApi
    {
        return new YounitedApi($clientId, $clientSecret, $resource);
    }

    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    protected function getContent()
    {
        return $this->getRequest()->getContent();
    }
}
