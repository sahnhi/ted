<?php

namespace App\Message;

class CaptainMetrics
{
    /** @var array */
    protected $post;

    /** @var string */
    protected $tag;

    public function __construct(array $post, string $tag)
    {
        $this->post = $post;
        $this->tag = $tag;
    }

    public function getPost(): array
    {
        return $this->post;
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}
