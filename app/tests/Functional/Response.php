<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class Response
{
    private readonly HttpResponse $response;

    public function __construct(
        private readonly KernelBrowser $client,
        private readonly Crawler $crawler
    ) {
    }

    public function getResponse(): HttpResponse
    {
        return $this->client->getResponse();
    }

    public function getResponseContent(): string
    {
        return $this->client->getResponse()->getContent();
    }

    public function getStatusCode(): int
    {
        return $this->client->getResponse()->getStatusCode();
    }

    public function getCrawler(): Crawler
    {
        return $this->crawler;
    }
}
