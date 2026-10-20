<?php

declare(strict_types=1);

namespace App\Edeliv\Client;

use App\HttpClient\HttpClientInterface;
use App\Utils\Url;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClient;

final class EdelivClient implements EdelivClientInterface
{
    // %s should be taskId
    public const URBANTZ_CANCEL_URL = '/bytaskid/%s/discard?removeFromRound=true';

    public function __construct(
        private readonly SymfonyHttpClient $httpClient,
        private readonly string $baseUri,
        private readonly string $apiKeyUrbantz,
        private readonly string $apiKeySecretUrbantz
    ) {
    }

    public function cancelTask(string $taskId): void
    {
        $response = $this->httpClient->request(
            HttpClientInterface::METHOD_POST,
            (string) $this->getCancelUri($taskId),
            [
                'auth_basic' => [$this->apiKeyUrbantz, $this->apiKeySecretUrbantz],
            ],
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf('http request return a %s code', $statusCode));
        }
    }

    private function getCancelUri(string $taskId): Url
    {
        return new Url(sprintf('%s%s', $this->baseUri, sprintf(self::URBANTZ_CANCEL_URL, $taskId)));
    }
}
