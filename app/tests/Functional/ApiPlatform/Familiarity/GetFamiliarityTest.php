<?php

declare(strict_types=1);

namespace app\tests\Functional\ApiPlatform\Familiarity;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GetFamiliarityTest extends ApiTestCase
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGet(): void
    {
        self::createClient()->request(Request::METHOD_GET, '/api/v2/familiarities',
            [
                'query' => [
                    'page' => 1,
                    'itemsPerPage' => 30,
                ],
            ]
        );

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/Familiarity',
            '@type' => 'hydra:Collection',
            'hydra:member' => [
                [
                    '@id' => '/api/v2/familiarities/20h-tf1',
                    '@type' => 'Familiarity',
                    'code' => '20h-tf1',
                    'name' => 'Le 20h de TF1 avec Pugadas',
                ],
            ],
        ]);
    }
}
