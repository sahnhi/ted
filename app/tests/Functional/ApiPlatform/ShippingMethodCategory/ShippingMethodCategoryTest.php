<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\ShippingMethodCategory;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ShippingMethodCategoryTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public static function setUpBeforeClass(): void
    {
        self::$dbPopulated = true;
        self::$purgeWithTruncate = false;
    }

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
    public function testGetEligibleShippingMethodCategories(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        // This cart has single orderItem with a mattress with a quantity of 1
        $client->request(Request::METHOD_GET, '/api/v2/shop/order/shipping-method-category/eligible', [
            'query' => [
                'tokenValue' => '5811cf75-84a3-4673-9cc3-69755d1b72aa',
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'hydra:member' => [
                [
                    '@id' => '/api/v2/shop/shipping-method-categories/ECONOMIQUE',
                    'code' => 'ECONOMIQUE',
                    'lowestPrice' => 1000,
                    'shippingMethods' => [
                        '/api/v2/shop/shipping-methods/VIR',
                    ],
                    'translations' => [
                        'fr_FR' => [
                            'name' => 'Livraison Economique',
                            'description' => 'Bien et pas cher!',
                            'claims' => 'Un acheté, un offert!',
                        ],
                    ],
                    'includedServices' => [],
                    // With the quantity of 1, only one ECO_ASSEMBLY and ECO_ASSEMBLY_RESTRICTED service should be returned
                    'paidServices' => [
                        [
                            'code' => 'ECO_ASSEMBLY',
                            'enabled' => true,
                            'included' => false,
                            'productVariant' => [
                                'code' => 'assembly_variant',
                                'product' => '/api/v2/shop/products/assembly',
                            ],
                            'translations' => [
                                'fr_FR' => [
                                    'name' => 'Assemblage Eco',
                                    'description' => 'Lorem Ipsum',
                                ],
                            ],
                        ],
                        [
                            'code' => 'ECO_ASSEMBLY_RESTRICTED',
                            'enabled' => true,
                            'included' => false,
                            'productVariant' => [
                                'code' => 'assembly_variant',
                                'product' => '/api/v2/shop/products/assembly',
                            ],
                            'translations' => [
                                'fr_FR' => [
                                    'name' => 'Assemblage Eco Restreint',
                                    'description' => 'Lorem Ipsum',
                                ],
                            ],
                        ],
                    ],
                    'enabledClaims' => [
                        [
                            'translations' => [
                                'fr_FR' => [
                                    'content' => 'This claim is enabled and NOT pushed',
                                ],
                            ],
                        ],
                        [
                            'translations' => [
                                'fr_FR' => [
                                    'content' => 'This claim is enabled and pushed',
                                ],
                            ],
                        ],
                    ],
                    'pushedClaims' => [
                        [
                            'translations' => [
                                'fr_FR' => [
                                    'content' => 'This claim is enabled and pushed',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'hydra:totalItems' => 1,
        ]);

        // We add another mattress to the order
        $client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-69755d1b72aa/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/TED140190', // Availability date set to 2024-12-01 00:00:00
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        $client->request(Request::METHOD_GET, '/api/v2/shop/order/shipping-method-category/eligible', [
            'query' => [
                'tokenValue' => '5811cf75-84a3-4673-9cc3-69755d1b72aa',
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'hydra:member' => [
                [
                    '@id' => '/api/v2/shop/shipping-method-categories/ECONOMIQUE',
                    'code' => 'ECONOMIQUE',
                    'lowestPrice' => 1000,
                    'shippingMethods' => [
                        '/api/v2/shop/shipping-methods/VIR',
                    ],
                    'translations' => [
                        'fr_FR' => [
                            'name' => 'Livraison Economique',
                            'description' => 'Bien et pas cher!',
                            'claims' => 'Un acheté, un offert!',
                        ],
                    ],
                    'includedServices' => [],
                    // With the quantity of 2, two distinct ECO_ASSEMBLY_RESTRICTED services should be returned
                    'paidServices' => [
                        [
                            'code' => 'ECO_ASSEMBLY',
                            'enabled' => true,
                            'included' => false,
                            'productVariant' => [
                                'code' => 'assembly_variant',
                                'product' => '/api/v2/shop/products/assembly',
                            ],
                            'translations' => [
                                'fr_FR' => [
                                    'name' => 'Assemblage Eco',
                                    'description' => 'Lorem Ipsum',
                                ],
                            ],
                        ],
                        [
                            'code' => 'ECO_ASSEMBLY_RESTRICTED',
                            'enabled' => true,
                            'included' => false,
                            'productVariant' => [
                                'code' => 'assembly_variant',
                                'product' => '/api/v2/shop/products/assembly',
                            ],
                            'translations' => [
                                'fr_FR' => [
                                    'name' => 'Assemblage Eco Restreint',
                                    'description' => 'Lorem Ipsum',
                                ],
                            ],
                        ],
                        [
                            'code' => 'ECO_ASSEMBLY_RESTRICTED',
                            'enabled' => true,
                            'included' => false,
                            'productVariant' => [
                                'code' => 'assembly_variant',
                                'product' => '/api/v2/shop/products/assembly',
                            ],
                            'translations' => [
                                'fr_FR' => [
                                    'name' => 'Assemblage Eco Restreint',
                                    'description' => 'Lorem Ipsum',
                                ],
                            ],
                        ],
                    ],
                    'enabledClaims' => [
                        [
                            'translations' => [
                                'fr_FR' => [
                                    'content' => 'This claim is enabled and NOT pushed',
                                ],
                            ],
                        ],
                        [
                            'translations' => [
                                'fr_FR' => [
                                    'content' => 'This claim is enabled and pushed',
                                ],
                            ],
                        ],
                    ],
                    'pushedClaims' => [
                        [
                            'translations' => [
                                'fr_FR' => [
                                    'content' => 'This claim is enabled and pushed',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'hydra:totalItems' => 1,
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testItCorrectlySetsTheSelectedShippingMethodCategoryOnTheOrderShipments(): void
    {
        static::createClient()->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-69755d1b72ad/shipping-method-category', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'shippingMethodCategoryCode' => 'ECONOMIQUE',
                'serviceCodes' => [],
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
                '@context' => '/api/v2/contexts/Order',
                '@id' => '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-69755d1b72ad',
                '@type' => 'Order',
                'giftCards' => [],
                'shippingAddress' => [
                    '@type' => 'Address',
                    'firstName' => 'customer2 firstname',
                    'lastName' => 'customer2 lastname',
                    'phoneNumber' => '0166666666',
                    'countryCode' => 'FR',
                    'street' => 'street 2',
                    'city' => 'paris',
                    'postcode' => '75000',
                ],
                'billingAddress' => [
                    '@type' => 'Address',
                    'firstName' => 'customer2 firstname',
                    'lastName' => 'customer2 lastname',
                    'phoneNumber' => '0166666666',
                    'countryCode' => 'FR',
                    'street' => 'street 2',
                    'city' => 'paris',
                    'postcode' => '75000',
                ],
                'payments' => [
                    [
                        '@type' => 'Payment',
                        'method' => [
                            '@id' => '/api/v2/shop/payment-methods/adyen_cb',
                            '@type' => 'PaymentMethod',
                        ],
                    ],
                ],
                'shipments' => [
                    [
                        '@type' => 'Shipment',
                        'shippingMethodCategory' => [
                            '@id' => '/api/v2/shop/shipping-method-categories/ECONOMIQUE',
                            '@type' => 'ShippingMethodCategory',
                            'code' => 'ECONOMIQUE',
                            'translations' => [
                                'fr_FR' => [
                                    '@type' => 'ShippingMethodCategoryTranslation',
                                    'name' => 'Livraison Economique',
                                ],
                            ],
                        ],
                        'method' => [
                            '@id' => '/api/v2/shop/shipping-methods/VIR',
                            '@type' => 'ShippingMethod',
                            'translations' => [
                                'fr_FR' => [
                                    '@type' => 'ShippingMethodTranslation',
                                    'name' => 'VIR',
                                ],
                            ],
                        ],
                    ],
                ],
                'currencyCode' => 'EUR',
                'localeCode' => 'fr_FR',
                'checkoutState' => 'addressed',
                'paymentState' => 'cart',
                'shippingState' => 'cart',
                'tokenValue' => '5811cf75-84a3-4673-9cc3-69755d1b72ad',
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
                        'adjustmentsRecursively' => [
                            [
                                '@type' => 'Adjustment',
                                'type' => 'tax',
                                'label' => 'TVA (20%)',
                                'amount' => 1150,
                                'neutral' => true,
                                'locked' => false,
                            ],
                        ],
                        'adjustmentsTotalRecursively' => 0,
                        'subtotal' => 6900,
                    ],
                    [
                        '@type' => 'OrderItem',
                        'variant' => [
                            '@id' => '/api/v2/shop/product-variants/recuperation_variant',
                            '@type' => 'ProductVariant',
                            'product' => [
                                '@id' => '/api/v2/shop/products/recuperation',
                                '@type' => 'Product',
                                'slug' => 'recuperation',
                                'lowestPrice' => 1000,
                                'defaultVariant' => '/api/v2/shop/product-variants/recuperation_variant',
                            ],
                            'price' => 1000,
                            'inStock' => true,
                            'originalPrice' => 2000,
                        ],
                        'productName' => 'Récupération',
                        'quantity' => 1,
                        'unitPrice' => 1000,
                        'total' => 0,
                        'adjustments' => [
                            [
                                '@type' => 'Adjustment',
                                'type' => 'shipping_method_service',
                                'label' => 'app.adjustment.types.shipping_method_service',
                                'amount' => 0,
                                'neutral' => true,
                                'locked' => false,
                            ],
                            [
                                '@type' => 'Adjustment',
                                'type' => 'offered_service',
                                'label' => 'app.adjustment.types.offered_service',
                                'amount' => -1000,
                                'neutral' => false,
                                'locked' => false,
                            ],
                        ],
                        'adjustmentsTotal' => -1000,
                        'adjustmentsRecursively' => [
                            [
                                '@type' => 'Adjustment',
                                'type' => 'shipping_method_service',
                                'label' => 'app.adjustment.types.shipping_method_service',
                                'amount' => 0,
                                'neutral' => true,
                                'locked' => false,
                            ],
                            [
                                '@type' => 'Adjustment',
                                'type' => 'offered_service',
                                'label' => 'app.adjustment.types.offered_service',
                                'amount' => -1000,
                                'neutral' => false,
                                'locked' => false,
                            ],
                        ],
                        'adjustmentsTotalRecursively' => -1000,
                        'subtotal' => 1000,
                    ],
                ],
                'itemsTotal' => 6900,
                'total' => 7900,
                'crossSellProducts' => [],
                'taxTotal' => 1150,
                'shippingTotal' => 1000,
                'orderPromotionTotal' => 0,
                'earliestDeliverySlot' => '2024-11-20T10:00:00+00:00',
            ]
        );
    }
}
