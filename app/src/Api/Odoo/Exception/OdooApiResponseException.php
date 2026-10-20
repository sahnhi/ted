<?php

declare(strict_types=1);

namespace App\Api\Odoo\Exception;

final class OdooApiResponseException extends \RuntimeException
{
    public const DUPLICATE_KEY_VALUE_VIOLATES_UNIQUE_CONSTRAINT = 'duplicate key value violates unique constraint';
    public const ORDER_ALREADY_CANCELLED = 'already cancelled';
    public const ORDER_ALREADY_VALIDATED = 'already validated';
    public const MULTIPLE_PICKINGS_FOUND = 'multiple pickings found';

    public function __construct(string $message)
    {
        parent::__construct("Odoo API response: $message", $this->guessCode($message));
    }

    private function guessCode(string $message): int
    {
        if (str_contains($message, self::DUPLICATE_KEY_VALUE_VIOLATES_UNIQUE_CONSTRAINT)) {
            return 403;
        }

        return 0;
    }
}
