<?php

namespace App\Api\TrustPilot\DTO;

use App\Api\TrustPilot\DTO\Review\Link;
use JMS\Serializer\Annotation\Type;

class Review
{
    /**
     * @var Link[]
     *
     * @Type("array<App\Api\TrustPilot\DTO\Review\Link>")
     */
    private $links;

    /**
     * @var Review\Review[]
     *
     * @Type("array<App\Api\TrustPilot\DTO\Review\Review>")
     */
    private $reviews;

    /**
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return Review\Review[]
     */
    public function getReviews(): array
    {
        return $this->reviews;
    }
}
