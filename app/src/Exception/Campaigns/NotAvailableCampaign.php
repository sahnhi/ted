<?php

declare(strict_types=1);

namespace App\Exception\Campaigns;

final class NotAvailableCampaign extends \RuntimeException
{
    public function __construct(
        string $message = 'Campaign is not available',
        private readonly int $httpStatusCode = 404,
        private readonly string $redirectUrl = '',
        $previous = null
    ) {
        parent::__construct($message, $httpStatusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }
}
