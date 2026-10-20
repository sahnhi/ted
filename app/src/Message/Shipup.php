<?php

namespace App\Message;

use App\Api\Shipup\DTO\ShipupMessageInterface;

class Shipup
{
    public function __construct(
        protected ShipupMessageInterface $subject,
        protected string $path,
        protected string $method = 'post'
    ) {
    }

    public function getSubject(): ShipupMessageInterface
    {
        return $this->subject;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
