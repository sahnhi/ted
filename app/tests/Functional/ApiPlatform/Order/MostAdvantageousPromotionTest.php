<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Order;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MostAdvantageousPromotionTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private HttpClientInterface $client;

    public static function setUpBeforeClass(): void
    {
        self::$dbPopulated = true;
        self::$purgeWithTruncate = false;
    }

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->disableReboot();
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testItDoesNotApplyThePromotionIfItIsLessAdvantageousThatTheCurrentDiscount(): void
    {
        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/discounted_product_half_discount', // Price: 50000, OriginalPrice: 100000
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'itemsTotal' => 50000,
            'total' => 51000,
            'orderPromotionTotal' => 0,
        ]);

        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/apply-coupon', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'couponCode' => 'promotion_15',
            ],
        ]);

        // 15% off the orignalPrice of 100000 is 15000, which is inferior to the reduction of the discounted price which is 50000
        // We therefore expect a validation error
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'couponCode: Ce code promo n\'est pas valide.',
            'violations' => [
                [
                    'propertyPath' => 'couponCode',
                    'message' => 'Ce code promo n\'est pas valide.',
                    'code' => null,
                ],
            ],
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testItAppliesThePromotionOnTheOriginalPriceAndNoThePriceIfMoreAdvantageous(): void
    {
        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/discounted_product_half_discount', // Price: 50000, OriginalPrice: 100000
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'itemsTotal' => 50000,
            'total' => 51000,
            'orderPromotionTotal' => 0,
        ]);

        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/apply-coupon', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'couponCode' => 'promotion_75',
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'itemsTotal' => 25000, // Equals to the originalPrice minus the reduction of 75% (100000 * (1 - 0.75))
            'total' => 26000,
            'orderPromotionTotal' => -75000,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testThePromotionIsOnlyAppliedToTheOrderItemsIfAdvantageous(): void
    {
        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/discounted_product_half_discount', // Price: 50000, OriginalPrice: 100000
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'itemsTotal' => 50000,
            'total' => 51000,
            'orderPromotionTotal' => 0,
        ]);

        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/TEDSO70190_TEDPBOIS15', // Price: 10000, OriginalPrice: null
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'itemsTotal' => 60000,
            'total' => 61000,
            'orderPromotionTotal' => 0,
        ]);

        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/apply-coupon', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'couponCode' => 'promotion_15',
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'items' => [
                [
                    '@type' => 'OrderItem',
                    'variant' => [
                            '@id' => '/api/v2/shop/product-variants/discounted_product_half_discount',
                            '@type' => 'ProductVariant',
                            'product' => [
                                      '@id' => '/api/v2/shop/products/discounted_product',
                                      '@type' => 'Product',
                                      'slug' => 'discounter-product',
                                      'lowestPrice' => 50000,
                                      'defaultVariant' => '/api/v2/shop/product-variants/discounted_product_half_discount',
                                ],
                            'price' => 50000,
                            'inStock' => true,
                            'originalPrice' => 100000,
                        ],
                    'productName' => 'Produit en promotion',
                    'quantity' => 1,
                    'unitPrice' => 50000,
                    'total' => 50000,
                    'subtotal' => 50000,
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
                            'price' => 10000,
                            'inStock' => true,
                            'originalPrice' => 10000,
                        ],
                    'productName' => 'Le sommier',
                    'quantity' => 1,
                    'unitPrice' => 10000,
                    'total' => 8500,
                    'subtotal' => 10000,
                ],
            ],
            'itemsTotal' => 58500,
            'total' => 59500,
            'taxTotal' => 9750,
            'shippingTotal' => 1000,
            'orderPromotionTotal' => -1500,
        ]);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testCouponThatGitProductsCanStillBeApplied(): void
    {
        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/discounted_product_half_discount', // Price: 50000, OriginalPrice: 100000
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'itemsTotal' => 50000,
            'total' => 51000,
            'orderPromotionTotal' => 0,
        ]);

        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/apply-coupon', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'couponCode' => 'BFCOUETTE', // This coupon is supposed to add a TED140190 variant for free
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'items' => [
                [
                    '@type' => 'OrderItem',
                    'variant' => [
                            '@id' => '/api/v2/shop/product-variants/discounted_product_half_discount',
                            '@type' => 'ProductVariant',
                            'product' => [
                                      '@id' => '/api/v2/shop/products/discounted_product',
                                      '@type' => 'Product',
                                      'slug' => 'discounter-product',
                                      'lowestPrice' => 50000,
                                      'defaultVariant' => '/api/v2/shop/product-variants/discounted_product_half_discount',
                                ],
                            'price' => 50000,
                            'inStock' => true,
                            'originalPrice' => 100000,
                        ],
                    'productName' => 'Produit en promotion',
                    'quantity' => 1,
                    'unitPrice' => 50000,
                    'total' => 50000,
                    'subtotal' => 50000,
                ],
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
                    'total' => 0,
                    'subtotal' => 6900,
                ],
            ],
            'itemsTotal' => 50000,
            'total' => 51000,
            'taxTotal' => 8333,
            'shippingTotal' => 1000,
            'orderPromotionTotal' => -6900,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testThePromotionIsAppliedOnThePriceOrOriginalPriceIfMoreAdvantageous(): void
    {
        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/discounted_product_half_discount', // Price: 50000, OriginalPrice: 100000
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'itemsTotal' => 50000,
            'total' => 51000,
            'orderPromotionTotal' => 0,
        ]);

        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/TEDSO70190_TEDPBOIS15', // Price: 10000, OriginalPrice: null
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'itemsTotal' => 60000,
            'total' => 61000,
            'orderPromotionTotal' => 0,
        ]);

        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/apply-coupon', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'couponCode' => 'promotion_75',
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'items' => [
                [
                    '@type' => 'OrderItem',
                    'variant' => [
                            '@id' => '/api/v2/shop/product-variants/discounted_product_half_discount',
                            '@type' => 'ProductVariant',
                            'product' => [
                                    '@id' => '/api/v2/shop/products/discounted_product',
                                    '@type' => 'Product',
                                    'slug' => 'discounter-product',
                                    'lowestPrice' => 50000,
                                    'defaultVariant' => '/api/v2/shop/product-variants/discounted_product_half_discount',
                                ],
                            'price' => 50000,
                            'inStock' => true,
                            'originalPrice' => 100000,
                        ],
                    'productName' => 'Produit en promotion',
                    'quantity' => 1,
                    'unitPrice' => 100000, // The price of 5000 reverted to the orignalPrice of 10000 to apply the 75% reduction (which is more than the 50% discount)
                    'total' => 25000,
                    'subtotal' => 100000,
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
                            'price' => 10000,
                            'inStock' => true,
                            'originalPrice' => 10000,
                        ],
                    'productName' => 'Le sommier',
                    'quantity' => 1,
                    'unitPrice' => 10000, // The price stayed the same since this product was not discounted
                    'total' => 2500,
                    'subtotal' => 10000,
                ],
            ],
            'itemsTotal' => 27500,
            'total' => 28500,
            'taxTotal' => 4584,
            'shippingTotal' => 1000,
            'orderPromotionTotal' => -82500,
        ]);
    }
}
