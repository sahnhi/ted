<?php

namespace App\Api\TrustPilot\DTO\Review\Review;

use App\Api\TrustPilot\DTO\Review\Link;
use JMS\Serializer\Annotation\Type;

class BusinessUnit
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
    private $identifyingName;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $displayName;

    /**
     * @var Link[]
     *
     * @Type("array<App\Api\TrustPilot\DTO\Review\Link>")
     */
    private $links;
}
