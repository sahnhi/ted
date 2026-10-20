<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Familiarity;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AddFamiliarityTest extends ApiTestCase
{
    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testPost(): void
    {
        self::createClient()->request(Request::METHOD_POST, '/api/v2/shop/customers/familiarities', [
            'json' => [
                'tokenValue' => '042f6288-1e61-48f9-aedc-3452463d0732',
                'familiarities' => ['20h-tf1', 'instagram'],
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/Customer',
            '@type' => 'Customer',
            'familiarities' => [
                [
                    '@type' => 'Familiarity',
                    'code' => '20h-tf1',
                    'translations' => [
                        'fr_FR' => [
                            '@type' => 'FamiliarityTranslation',
                            'wording' => 'Le 20h de TF1 avec Pugadas',
                        ],
                    ],
                ],
                [
                    '@type' => 'Familiarity',
                    'code' => 'instagram',
                    'translations' => [
                        'fr_FR' => [
                            '@type' => 'FamiliarityTranslation',
                            'wording' => 'Instagram',
                        ],
                    ],
                ],
            ],
            'defaultAddress' => null,
            'user' => null,
            'email' => 'customer1@email.com',
            'firstName' => 'customer1 firstname',
            'lastName' => 'customer1 lastname',
            'subscribedToNewsletter' => false,
            'fullName' => 'customer1 firstname customer1 lastname',
        ]);
    }
}
