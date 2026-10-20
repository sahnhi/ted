<?php

namespace App\Message;

class Drip
{
    /** @var string */
    protected $apiUrl;

    /** @var array */
    protected $post;

    public function __construct(string $apiUrl, array $post)
    {
        $this->apiUrl = $apiUrl;
        $this->post = $post;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getPost(): array
    {
        return $this->post;
    }
}
