<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Order;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Doctrine\Entity\Sylius\Order;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetOrderTest extends ApiTestCase
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
    public function testGet(): void
    {
        $this->client->request('GET', '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-69755d1b72aa');

        self::assertResponseIsSuccessful();

        self::assertMatchesResourceItemJsonSchema(Order::class);

        self::assertJsonContains([
            'shippingAddress' => [
                'firstName' => 'customer1 firstname',
                'lastName' => 'customer1 lastname',
                'phoneNumber' => '0166666666',
                'countryCode' => 'FR',
                'street' => 'street 1',
                'city' => 'paris',
                'postcode' => '75000',
                'details' => 'Lorem Ipsum',
                'fullName' => 'customer1 firstname customer1 lastname',
                'tva' => 'TVA_NUMBER',
                'company' => 'Wayne Enterprise',
            ],
            'tokenValue' => '5811cf75-84a3-4673-9cc3-69755d1b72aa',
            'items' => [
                [
                    'variant' => [
                        '@id' => '/api/v2/shop/product-variants/TED140190',
                        '@type' => 'ProductVariant',
                        'product' => [
                            '@id' => '/api/v2/shop/products/mattress',
                            '@type' => 'Product',
                            'slug' => 'mattress',
                            'lowestPrice' => 6900,
                            'defaultVariant' => '/api/v2/shop/product-variants/TED160200',
                        ],
                        'totalEcotaxes' => 550,
                        'price' => 6900,
                        'inStock' => true,
                        'originalPrice' => 6900,
                        'fullOptionValue' => '140 cm x 190 cm x 25 cm',
                    ],
                    'productName' => 'matelas',
                    'quantity' => 1,
                    'unitPrice' => 6900,
                    'total' => 6900,
                    'adjustments' => [],
                    'adjustmentsTotal' => 0,
                    'adjustmentsRecursively' => [],
                    'adjustmentsTotalRecursively' => 0,
                    'subtotal' => 6900,
                    'availableFrom' => '2024-11-25T14:30:37+00:00',
                ],
            ],
            'crossSellProducts' => [],
            'customerEmail' => 'customer1@email.com',
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetByEmailAndOrderNumberWithMatchingEmailAndOrderNumber(): void
    {
        $this->client->request('GET', '/api/v2/shop/orders/email-number', [
            'query' => [
                'email' => 'customer1@email.com',
                'orderNumber' => '00001',
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertMatchesResourceCollectionJsonSchema(Order::class);

        self::assertJsonContains(
            [
                '@context' => '/api/v2/contexts/Order',
                '@id' => '/api/v2/shop/orders',
                '@type' => 'hydra:Collection',
                'hydra:member' => [
                    [
                        '@id' => '/api/v2/shop/orders/042f6288-1e61-48f9-aedc-3452463d0742',
                        '@type' => 'Order',
                        'payments' => [],
                        'shipments' => [],
                        'currencyCode' => 'EUR',
                        'localeCode' => 'fr_FR',
                        'checkoutState' => 'completed',
                        'paymentState' => 'paid',
                        'shippingState' => 'ready',
                        'tokenValue' => '042f6288-1e61-48f9-aedc-3452463d0742',
                        'number' => '00001',
                        'items' => [
                            [
                                '@type' => 'OrderItem',
                                'variant' => [
                                    '@id' => '/api/v2/shop/product-variants/TED140190',
                                    '@type' => 'ProductVariant',
                                    'product' => [
                                        '@id' => '/api/v2/shop/products/mattress',
                                        '@type' => 'Product',
                                        'slug' => 'mattress',
                                        'lowestPrice' => 6900,
                                        'defaultVariant' => '/api/v2/shop/product-variants/TED160200',
                                    ],
                                    'price' => 6900,
                                    'inStock' => true,
                                    'originalPrice' => 6900,
                                ],
                                'productName' => 'matelas',
                                'quantity' => 1,
                                'unitPrice' => 6900,
                                'total' => 6900,
                                'adjustments' => [],
                                'adjustmentsTotal' => 0,
                                'adjustmentsRecursively' => [],
                                'adjustmentsTotalRecursively' => 0,
                                'subtotal' => 6900,
                            ],
                        ],
                        'itemsTotal' => 6900,
                        'total' => 6900,
                        'crossSellProducts' => [],
                        'coupon' => [
                            'promotion 1' => [],
                        ],
                        'taxTotal' => 0,
                        'shippingTotal' => 0,
                        'orderPromotionTotal' => 0,
                        'customerEmail' => 'customer1@email.com',
                    ],
                ],
                'hydra:totalItems' => 1,
                'hydra:view' => [
                    '@id' => '/api/v2/shop/orders/email-number?email=customer1%40email.com&orderNumber=00001',
                    '@type' => 'hydra:PartialCollectionView',
                ],
            ]
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetByEmailAndOrderNumberWithUnMatchingEmailAndOrderNumber(): void
    {
        $this->client->request('GET', '/api/v2/shop/orders/email-number', [
            'query' => [
                'email' => 'customer1@email.com',
                'orderNumber' => '00002', // The valid order number is '00001'
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertMatchesResourceCollectionJsonSchema(Order::class);

        self::assertJsonContains(
            [
                '@context' => '/api/v2/contexts/Order',
                '@id' => '/api/v2/shop/orders',
                '@type' => 'hydra:Collection',
                'hydra:member' => [],
                'hydra:totalItems' => 0,
                'hydra:view' => [
                    '@id' => '/api/v2/shop/orders/email-number?email=customer1%40email.com&orderNumber=00002',
                    '@type' => 'hydra:PartialCollectionView',
                ],
            ]
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testItReturnsTheShippingStockAvailabilityDate(): void
    {
        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/PACK_LIT_ADULTE', // Availability date set to 2024-12-01 00:00:00
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/Order',
            '@id' => '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as',
            '@type' => 'Order',
            'giftCards' => [],
            'payments' => [
                [
                    '@type' => 'Payment',
                    'method' => [
                        '@id' => '/api/v2/shop/payment-methods/adyen_cb',
                        '@type' => 'PaymentMethod',
                    ],
                ],
            ],
            'shipments' => [],
            'currencyCode' => 'EUR',
            'localeCode' => 'fr_FR',
            'checkoutState' => 'cart',
            'paymentState' => 'cart',
            'shippingState' => 'cart',
            'tokenValue' => '5811cf75-84a3-4673-9cc3-00755d1b72as',
            'items' => [
                [
                    '@type' => 'OrderItem',
                    'variant' => [
                        '@id' => '/api/v2/shop/product-variants/PACK_LIT_ADULTE',
                        '@type' => 'ProductVariant',
                        'product' => [
                            '@id' => '/api/v2/shop/products/bundle-lit-adult',
                            '@type' => 'Product',
                            'slug' => 'pack-lit-adulte',
                            'lowestPrice' => 9000,
                            'defaultVariant' => '/api/v2/shop/product-variants/PACK_LIT_ADULTE',
                        ],
                        'totalEcotaxes' => 0,
                        'price' => 9000,
                        'inStock' => true,
                        'originalPrice' => 9000,
                    ],
                    'productName' => 'Le pack lit adulte',
                    'quantity' => 1,
                    'unitPrice' => 9000,
                    'total' => 8100,
                    'adjustments' => [],
                    'adjustmentsTotal' => 0,
                    'adjustmentsRecursively' => [
                        [
                            '@type' => 'Adjustment',
                            'type' => 'order_unit_promotion',
                            'label' => 'pack lit promotion',
                            'amount' => -900,
                            'neutral' => false,
                            'locked' => false,
                        ],
                        [
                            '@type' => 'Adjustment',
                            'type' => 'tax',
                            'label' => 'TVA (20%)',
                            'amount' => 1350,
                            'neutral' => true,
                            'locked' => false,
                        ],
                    ],
                    'adjustmentsTotalRecursively' => -900,
                    'subtotal' => 8100,
                    'availableFrom' => '2024-12-01T00:00:00+00:00',
                ],
            ],
            'itemsTotal' => 8100,
            'total' => 8100,
            'crossSellProducts' => [],
            'taxTotal' => 1350,
            'shippingTotal' => 0,
            'orderPromotionTotal' => -900,
            'customerEmail' => 'customer1@email.com',
        ]);
    }
}
