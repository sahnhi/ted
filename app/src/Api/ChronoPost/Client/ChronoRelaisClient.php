<?php

declare(strict_types=1);

namespace App\Api\ChronoPost\Client;

use App\Api\ChronoPost\Exception\ChronoRelaisHttpClientFailed;
use App\Api\ChronoPost\Query\ChronoRelaisQuery;
use App\HttpClient\HttpClientInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClient;

final class ChronoRelaisClient implements ChronoRelaisClientInterface
{
    private const FIND_CHRONO_POINTS = 'recherchebt-ws-cxf/PointRelaisServiceWS/recherchePointChronopostInterParService';

    public function __construct(
        private readonly SymfonyHttpClient $httpClient,
        private readonly string $baseUri,
        private readonly string $accountNumber,
        private readonly string $password,
        private readonly DecoderInterface $decoder
    ) {
    }

    public function findNearBy(ChronoRelaisQuery $chronoRelaisQuery): array
    {
        $queryParams = array_merge(
            [
                'accountNumber' => $this->accountNumber,
                'password' => $this->password,
            ],
            $chronoRelaisQuery->toArray()
        );

        $response = $this->httpClient->request(
            HttpClientInterface::METHOD_GET,
            $this->baseUri . self::FIND_CHRONO_POINTS,
            ['query' => $queryParams]
        );

        $soapResponseAsArray = $this->decoder->decode($response->getContent(), 'xml');

        if (isset($soapResponseAsArray['soap:Body']['ns1:recherchePointChronopostInterParServiceResponse']['return']['listePointRelais'])) {
            return $soapResponseAsArray['soap:Body']['ns1:recherchePointChronopostInterParServiceResponse']['return']['listePointRelais'];
        }

        if (isset($soapResponseAsArray['soap:Body']['ns1:recherchePointChronopostInterParServiceResponse']['return']['errorCode']) && '601' === $soapResponseAsArray['soap:Body']['ns1:recherchePointChronopostInterParServiceResponse']['return']['errorCode']) {
            return [];
        }

        throw new ChronoRelaisHttpClientFailed();
    }
}
