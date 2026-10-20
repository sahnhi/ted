<?php

namespace App\Api\TrustPilot\DTO\Review\Review;

use App\Api\TrustPilot\DTO\Review\Link;
use JMS\Serializer\Annotation\Type;

class Consumer
{
    /**
     * @var string
     *
     * @Type("string")
     */
    private $id;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $displayName;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $displayLocation;

    /**
     * @var int
     *
     * @Type("int")
     */
    private $numberOfReviews;

    /**
     * @var Link[]
     *
     * @Type("array<App\Api\TrustPilot\DTO\Review\Link>")
     */
    private $links;

    public function getId(): string
    {
        return $this->id;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getDisplayLocation(): string
    {
        return $this->displayLocation;
    }

    public function getNumberOfReviews(): int
    {
        return $this->numberOfReviews;
    }

    /**
     * @return Link[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }
}
