<?php

namespace App\Message;

class Invoice
{
    /** @var string */
    private $content;

    public function __construct(
        private readonly string $number,
        private readonly string $filename,
        string $content
    ) {
        $this->content = base64_encode($content);
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getContent(): string
    {
        return base64_decode($this->content);
    }
}
