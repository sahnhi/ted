<?php

declare(strict_types=1);

namespace App\ApiPlatform\Model;

final class ProductReviewStatsSummary
{
    public function __construct(
        private readonly string $productCode = '',
        private readonly int $totalReviews = 0,
        private readonly float $averageRating = 0,
        private readonly array $ratingDistribution = []
    ) {
    }

    public function getProductCode(): string
    {
        return $this->productCode;
    }

    public function getTotalReviews(): int
    {
        return $this->totalReviews;
    }

    public function getAverageRating(): float
    {
        return $this->averageRating;
    }

    public function getRatingDistribution(): array
    {
        return $this->ratingDistribution;
    }
}
