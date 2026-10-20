<?php

namespace App\Api\Odoo\Message;

use App\Api\Odoo\Model\ObjectMessageOdooInterface;

class Odoo
{
    protected $subject;

    /** @var string */
    protected $apiCall;

    /** @var string */
    protected $method;

    public function __construct(?ObjectMessageOdooInterface $subject, string $apiCall, string $method = 'post')
    {
        $this->subject = $subject;
        $this->apiCall = $apiCall;
        $this->method = $method;
    }

    public function getSubject(): ?ObjectMessageOdooInterface
    {
        return $this->subject;
    }

    public function getApiCall(): string
    {
        return $this->apiCall;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
