<?php

namespace App\Message;

class Mail
{
    /** @var string */
    protected $type;
    /** @var array */
    protected $recipients;
    /** @var array */
    protected $data = [];

    public function __construct(
        string $type,
        protected $order,
        array $recipients = []
    ) {
        $this->type = $type;
        $this->recipients = $recipients;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }
}
