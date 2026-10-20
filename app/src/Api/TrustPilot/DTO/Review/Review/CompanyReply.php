<?php

namespace App\Api\TrustPilot\DTO\Review\Review;

use DateTime;
use JMS\Serializer\Annotation\Type;

class CompanyReply
{
    /**
     * @var string
     *
     * @Type("string")
     */
    private $text;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $authorBusinessUserId;

    /**
     * @var \DateTime
     *
     * @Type("DateTime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @Type("DateTime")
     */
    private $updatedAt;
}
