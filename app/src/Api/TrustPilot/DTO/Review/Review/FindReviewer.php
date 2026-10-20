<?php

namespace App\Api\TrustPilot\DTO\Review\Review;

use App\Api\TrustPilot\DTO\Review\Review\FindReviewer\Request;
use JMS\Serializer\Annotation\Type;

class FindReviewer
{
    /**
     * @var bool
     *
     * @Type("bool")
     */
    private $isEligible;

    /**
     * @var Request[]
     *
     * @Type("array<App\Api\TrustPilot\DTO\Review\Review\FindReviewer\Request>")
     */
    private $requests;
}
