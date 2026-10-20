<?php

declare(strict_types=1);

namespace App\View;

class RelatedProductView
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?int $originalLowerPrice = null,
        public readonly ?int $lowerPrice = null,
        public readonly ?string $description = null,
        public readonly ?string $shortDescription = null,
        public readonly ?string $gtin = null,
        public ?string $averageRating = null,
        public readonly array $images = [],
    ) {
    }
}
