<?php

namespace App\Api\TrustPilot\DTO\Review\Review;

use JMS\Serializer\Annotation\Type;

class Location
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
    private $name;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $urlFormattedName;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $externalId;
}
