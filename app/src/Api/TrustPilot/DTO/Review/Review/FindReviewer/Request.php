<?php

namespace App\Api\TrustPilot\DTO\Review\Review\FindReviewer;

use App\Api\TrustPilot\DTO\Review\Review\FindReviewer\Request\ConsumerResponse;
use DateTime;
use JMS\Serializer\Annotation\Type;

class Request
{
    /**
     * @var string
     *
     * @Type("string")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @Type("DateTime")
     */
    private $created;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $status;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $businessUserMessage;

    /**
     * @var ConsumerResponse
     *
     * @Type("App\Api\TrustPilot\DTO\Review\Review\FindReviewer\Request\ConsumerResponse")
     */
    private $consumerResponse;
}
