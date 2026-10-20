<?php

declare(strict_types=1);

namespace App\Model\Familiarity;

class AddFamiliarities
{
    public function __construct(
        public readonly string $tokenValue,
        /**
         * @var array<string>
         */
        public readonly array $familiarities,
    ) {
    }
}
