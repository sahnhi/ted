<?php

namespace App\Api\TrustPilot\DTO\Review\Review\FindReviewer\Request;

use DateTime;
use JMS\Serializer\Annotation\Type;

class ConsumerResponse
{
    /**
     * @var \DateTime
     *
     * @Type("DateTime")
     */
    private $submittedAt;

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
    private $email;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $address;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $referenceId;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $message;
}
