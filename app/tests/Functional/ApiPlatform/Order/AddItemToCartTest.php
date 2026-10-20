<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Order;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AddItemToCartTest extends ApiTestCase
{
    private HttpClientInterface $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testAddProductItemAndAddonsToCart(): void
    {
        // 5811cf75-84a3-4673-9cc3-00755d1b72ad is an empty cart
        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72ad/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/TED160200',
                'quantity' => 1,
                'addons' => [
                    [
                        'productVariant' => 'TEDSO70190_TEDPBOIS15',
                        'quantity' => 10,
                    ],
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/Order',
            '@id' => '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72ad',
            '@type' => 'Order',
            'currencyCode' => 'EUR',
            'localeCode' => 'fr_FR',
            'checkoutState' => 'cart',
            'paymentState' => 'cart',
            'shippingState' => 'cart',
            'tokenValue' => '5811cf75-84a3-4673-9cc3-00755d1b72ad',
            'items' => [
                [
                    '@type' => 'OrderItem',
                    'variant' => [
                        '@id' => '/api/v2/shop/product-variants/TED160200',
                        '@type' => 'ProductVariant',
                        'product' => [
                            '@id' => '/api/v2/shop/products/mattress',
                            '@type' => 'Product',
                            'slug' => 'mattress',
                            'lowestPrice' => 6900,
                            'defaultVariant' => '/api/v2/shop/product-variants/TED160200',
                        ],
                        'totalEcotaxes' => 0,
                        'fullOptionValue' => '140 cm x 190 cm x 25 cm',
                        'price' => 7500,
                        'inStock' => true,
                        'originalPrice' => 7500,
                    ],
                    'productName' => 'matelas',
                    'quantity' => 1,
                    'unitPrice' => 7500,
                    'total' => 7500,
                    'adjustments' => [
                    ],
                    'adjustmentsTotal' => 0,
                    'adjustmentsTotalRecursively' => 0,
                    'subtotal' => 7500,
                    'availableFrom' => '2024-11-25T14:30:37+00:00',
                ],
                [
                    '@type' => 'OrderItem',
                    'variant' => [
                        '@id' => '/api/v2/shop/product-variants/TEDSO70190_TEDPBOIS15',
                        '@type' => 'ProductVariant',
                        'product' => [
                            '@id' => '/api/v2/shop/products/bed-frame',
                            '@type' => 'Product',
                            'slug' => 'sommier',
                            'lowestPrice' => 10000,
                            'defaultVariant' => '/api/v2/shop/product-variants/TEDSO70190_TEDPBOIS15',
                        ],
                        'totalEcotaxes' => 0,
                        'price' => 10000,
                        'inStock' => true,
                        'originalPrice' => 10000,
                    ],
                    'productName' => 'Le sommier',
                    'quantity' => 10,
                    'unitPrice' => 10000,
                    'total' => 100000,
                    'adjustments' => [
                    ],
                    'adjustmentsTotal' => 0,
                    'adjustmentsTotalRecursively' => 0,
                    'subtotal' => 100000,
                    'availableFrom' => '2024-11-25T14:30:37+00:00',
                ],
            ],
            'itemsTotal' => 107500,
            'total' => 108500,
            'crossSellProducts' => [
            ],
            'taxTotal' => 17917,
            'shippingTotal' => 1000,
            'orderPromotionTotal' => 0,
        ]);
    }
}
