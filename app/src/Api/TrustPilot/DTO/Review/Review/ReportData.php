<?php

namespace App\Api\TrustPilot\DTO\Review\Review;

use DateTime;
use JMS\Serializer\Annotation\Type;

class ReportData
{
    /**
     * @var string
     *
     * @Type("string")
     */
    private $source;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $publicComment;

    /**
     * @var \DateTime
     *
     * @Type("DateTime")
     */
    private $createdAt;

    /**
     * @var array
     *
     * @Type("array")
     */
    private $reasons = [];
}
