<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Product;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ProductVariantTest extends ApiTestCase
{
    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetVariant(): void
    {
        self::createClient()->request('GET', '/api/v2/shop/product-variants/TED140190');

        self::assertResponseIsSuccessful();

        self::assertJsonContains(
            [
                '@context' => '/api/v2/contexts/ProductVariant',
                '@id' => '/api/v2/shop/product-variants/TED140190',
                '@type' => 'ProductVariant',
                'bundleAssociations' => [],
                'packs' => [
                        [
                            '@id' => '/api/v2/shop/packs/example-pack',
                            '@type' => 'Pack',
                            'code' => 'example-pack',
                        ],
                    ],
                'code' => 'TED140190',
                'product' => '/api/v2/shop/products/mattress',
                'optionValues' => [],
                'translations' => [
                        'fr_FR' => [
                            '@type' => 'ProductVariantTranslation',
                            'name' => 'MATELAS TEDIBER 140cm x 200cm',
                            'locale' => 'fr_FR',
                        ],
                    ],
                'shippingCategoryClaims' => null,
                'productDefaultName' => 'matelas',
                'price' => 6900,
                'inStock' => true,
                'originalPrice' => 6900,
                'enabled' => true,
            ]
        );
    }
}
