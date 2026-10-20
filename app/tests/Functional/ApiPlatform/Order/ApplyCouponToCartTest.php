<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Order;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ApplyCouponToCartTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public static function setUpBeforeClass(): void
    {
        self::$dbPopulated = true;
        self::$purgeWithTruncate = false;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testPatchGiftCard(): void
    {
        self::createClient()->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-69755d1b72aa/apply-coupon', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'couponCode' => 'C55DF89E8898B2EF7BE3',
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/Order',
            '@id' => '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-69755d1b72aa',
            '@type' => 'Order',
            'giftCards' => [
                [
                    '@type' => 'GiftCard',
                    'code' => 'C55DF89E8898B2EF7BE3',
                    'initialAmount' => 1000,
                    'amount' => 1000,
                    'message' => 'All your base are belong to us',
                ],
            ],
            'shippingAddress' => [
                '@type' => 'Address',
                'firstName' => 'customer1 firstname',
                'lastName' => 'customer1 lastname',
                'phoneNumber' => '0166666666',
                'countryCode' => 'FR',
                'street' => 'street 1',
                'city' => 'paris',
                'postcode' => '75000',
            ],
            'billingAddress' => [
                '@type' => 'Address',
                'firstName' => 'customer1 firstname',
                'lastName' => 'customer1 lastname',
                'phoneNumber' => '0166666666',
                'countryCode' => 'FR',
                'street' => 'street 1',
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
            'checkoutState' => 'cart',
            'paymentState' => 'cart',
            'shippingState' => 'cart',
            'tokenValue' => '5811cf75-84a3-4673-9cc3-69755d1b72aa',
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
            ],
            'itemsTotal' => 6900,
            'total' => 6900,
            'crossSellProducts' => [],
            'taxTotal' => 1150,
            'shippingTotal' => 1000,
            'orderPromotionTotal' => 0,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testPatchCoupon(): void
    {
        self::createClient()->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-69755d1b72aa/apply-coupon', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'couponCode' => 'promotion_15',
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/Order',
            '@id' => '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-69755d1b72aa',
            '@type' => 'Order',
            'giftCards' => [],
            'shippingAddress' => [
                '@type' => 'Address',
                'firstName' => 'customer1 firstname',
                'lastName' => 'customer1 lastname',
                'phoneNumber' => '0166666666',
                'countryCode' => 'FR',
                'street' => 'street 1',
                'city' => 'paris',
                'postcode' => '75000',
            ],
            'billingAddress' => [
                '@type' => 'Address',
                'firstName' => 'customer1 firstname',
                'lastName' => 'customer1 lastname',
                'phoneNumber' => '0166666666',
                'countryCode' => 'FR',
                'street' => 'street 1',
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
            'checkoutState' => 'cart',
            'paymentState' => 'cart',
            'shippingState' => 'cart',
            'tokenValue' => '5811cf75-84a3-4673-9cc3-69755d1b72aa',
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
                    'total' => 5865,
                    'adjustments' => [],
                    'adjustmentsTotal' => 0,
                    'adjustmentsRecursively' => [
                        [
                            '@type' => 'Adjustment',
                            'type' => 'order_promotion',
                            'label' => '15off',
                            'amount' => -1035,
                            'neutral' => false,
                            'locked' => false,
                        ],
                        [
                            '@type' => 'Adjustment',
                            'type' => 'tax',
                            'label' => 'TVA (20%)',
                            'amount' => 978,
                            'neutral' => true,
                            'locked' => false,
                        ],
                    ],
                    'adjustmentsTotalRecursively' => -1035,
                    'subtotal' => 6900,
                ],
            ],
            'itemsTotal' => 5865,
            'total' => 6865,
            'crossSellProducts' => [],
            'coupon' => [
                '15off' => [
                    [
                        '@type' => 'PromotionAction',
                        'type' => 'unit_percentage_discount_with_exclusion',
                        'configuration' => [
                            'percentage' => 0.15,
                        ],
                    ],
                ],
            ],
            'taxTotal' => 978,
            'shippingTotal' => 1000,
            'orderPromotionTotal' => -1035,
            'promotionCouponCode' => 'promotion_15',
        ]);
    }
}
