<?php

declare(strict_types=1);

namespace App\Api\AdyenPayByLink\Client;

use App\Api\AdyenPayByLink\Exception\AdyenPayByLinkHttpClientFailed;
use App\Api\AdyenPayByLink\Query\AdyenPayByLinkQuery;
use App\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClient;

final class AdyenPayByLinkClient
{
    private const CREATE_PAYMENT_LINK = '/paymentLinks';

    public function __construct(
        private readonly SymfonyHttpClient $httpClient
    ) {
    }

    public function createLink(AdyenPayByLinkQuery $adyenPayByLinkQuery, string $baseUrl, string $apiKey): array
    {
        try {
            $response = $this->httpClient->request(
                HttpClientInterface::METHOD_POST,
                $baseUrl . self::CREATE_PAYMENT_LINK,
                [
                    'headers' => ['x-API-key' => $apiKey],
                    'json' => $adyenPayByLinkQuery->toArray(),
                ]
            );
        } catch (\Exception) {
            throw new AdyenPayByLinkHttpClientFailed();
        }

        if (HttpClientInterface::HTTP_CODE_CREATED !== $response->getStatusCode()) {
            throw new AdyenPayByLinkHttpClientFailed();
        }

        return json_decode($response->getContent(), true);
    }
}
