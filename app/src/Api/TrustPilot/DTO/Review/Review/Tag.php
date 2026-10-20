<?php

namespace App\Api\TrustPilot\DTO\Review\Review;

use JMS\Serializer\Annotation\Type;

class Tag
{
    /**
     * @var string
     *
     * @Type("string")
     */
    private $group;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $value;
}
