<?php

declare(strict_types=1);

namespace App\Tests\Unit\Edeliv;

use App\Edeliv\Client\EdelivClient;
use App\HttpClient\HttpClientInterface;
use App\Tests\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class EdelivClientTest extends TestCase
{
    public function testCancelTask(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $taskId = '123';
        $baseUri = 'https://www.tediber.com';
        $apiKeyUrbantz = 'urbantz';
        $apiKeySecretUrbantz = 'urbantzSecret';
        $uri = 'https://www.tediber.com/bytaskid/123/discard?removeFromRound=true';

        $response = $this->createMock(ResponseInterface::class);
        $client = new EdelivClient($httpClient, $baseUri, $apiKeyUrbantz, $apiKeySecretUrbantz);

        $response->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);

        $httpClient->expects(self::once())
            ->method('request')
            ->with(HttpClientInterface::METHOD_POST, $uri, ['auth_basic' => [$apiKeyUrbantz, $apiKeySecretUrbantz]])
            ->willReturn($response);

        $client->cancelTask($taskId);
    }
}
