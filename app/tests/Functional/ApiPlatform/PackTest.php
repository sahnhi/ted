<?php

namespace App\Tests\Functional\ApiPlatform;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PackTest extends ApiTestCase
{
    public function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetPack(): void
    {
        static::createClient()->request(Request::METHOD_GET, '/api/v2/shop/packs/example-pack');

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/Pack',
            '@id' => '/api/v2/shop/packs/example-pack',
            '@type' => 'Pack',
            'code' => 'example-pack',
            'addons' => [
                [
                    '@id' => '/api/v2/shop/product-variants/TED160200',
                    '@type' => 'ProductVariant',
                    'product' => '/api/v2/shop/products/mattress',
                    'price' => 7500,
                    'inStock' => true,
                    'originalPrice' => 7500,
                ],
            ],
            'translations' => [
                    'fr_FR' => [
                            '@type' => 'PackTranslation',
                            'name' => 'example pack',
                        ],
                ],
        ]);
    }
}
