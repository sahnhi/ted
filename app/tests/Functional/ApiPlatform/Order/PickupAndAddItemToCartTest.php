<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Order;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PickupAndAddItemToCartTest extends ApiTestCase
{
    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testItPicksUpANewCartAndAddTheOrderItemToTheNewlyCreatedCart(): void
    {
        $client = static::createClient();

        $response = $client->request(Request::METHOD_POST, '/api/v2/shop/orders/pickup-and-add-item', [
            'json' => [
                'localeCode' => 'fr_FR',
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
            '@type' => 'Order',
            'currencyCode' => 'EUR',
            'localeCode' => 'fr_FR',
            'checkoutState' => 'cart',
            'paymentState' => 'cart',
            'shippingState' => 'cart',
            'items' => [
                [
                    'variant' => [
                        '@id' => '/api/v2/shop/product-variants/TED160200',
                    ],
                    'quantity' => 1,
                ],
                [
                    'variant' => [
                        '@id' => '/api/v2/shop/product-variants/TEDSO70190_TEDPBOIS15',
                    ],
                    'quantity' => 10,
                ],
            ],
            'itemsTotal' => 107500,
            'total' => 108500,
            'crossSellProducts' => [],
            'taxTotal' => 17917,
            'shippingTotal' => 1000,
            'orderPromotionTotal' => 0,
        ]);

        $order = $response->toArray();

        self::assertNotEmpty($order['tokenValue']);
    }
}
