<?php

namespace App\Api\TrustPilot\DTO\Review\Review;

use DateTime;
use JMS\Serializer\Annotation\Type;

class BusinessUnitHistory
{
    /**
     * @var string
     *
     * @Type("string")
     */
    private $businessUnitId;

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
     * @var \DateTime
     *
     * @Type("DateTime")
     */
    private $changeDate;
}
