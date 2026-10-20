<?php

namespace App\Bridge;

use App\Legacy\Younited\YounitedApi;

interface YounitedBridgeInterface
{
    public function getClientId(): string;

    public function setClientId(string $clientId);

    public function getClientSecret(): string;

    public function setClientSecret(string $clientSecret);

    public function getResource(): string;

    public function setResource(string $resource);

    public function getEnvironment(): string;

    public function setEnvironment(string $environment);

    public function isPostMethod(): bool;

    public function getDecisionResult();

    public function getReasonCode();

    public function createYounited($clientId, $clientSecret, $resource): YounitedApi;
}
