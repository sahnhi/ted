<?php

namespace App\HttpClient;

use Symfony\Contracts\HttpClient\HttpClientInterface as HttpClientSymfony;

interface HttpClientInterface extends HttpClientSymfony
{
    public const METHOD_POST = 'POST';
    public const METHOD_GET = 'GET';
    public const HTTP_CODE_CREATED = 201;
}
