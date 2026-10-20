<?php

namespace App\Api\TrustPilot\DTO\Review;

use JMS\Serializer\Annotation\Type;

class Link
{
    public const REL_NEXT_PAGE = 'next-page';

    /**
     * @var string
     *
     * @Type("string")
     */
    private $href;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $method;

    /**
     * @var string
     *
     * @Type("string")
     */
    private $rel;

    public function getHref(): string
    {
        return $this->href;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRel(): string
    {
        return $this->rel;
    }
}
