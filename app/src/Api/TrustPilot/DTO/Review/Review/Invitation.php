<?php

namespace App\Api\TrustPilot\DTO\Review\Review;

use JMS\Serializer\Annotation\Type;

class Invitation
{
    /**
     * @var string
     *
     * @Type("string")
     */
    private $businessUnitId;
}
