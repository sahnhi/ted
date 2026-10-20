<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Order;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BlackFridayTest extends ApiTestCase
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
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testTheBlackFridayPromotionIsNotAppliedWhenIDidNotUseTheBlackFridayCoupon(): void
    {
        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/TEDSO70190_TEDPBOIS15',
                'quantity' => 3,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'itemsTotal' => 30000,
            'shippingTotal' => 1000,
            'total' => 31000,
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
    public function testTheBlackFridayPromotionIsAppliedWhenIDidUseTheBlackFridayCoupon(): void
    {
        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/apply-coupon', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'couponCode' => 'BLACKFRIDAY',
            ],
        ]);

        self::assertResponseIsSuccessful();

        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/TEDSO70190_TEDPBOIS15',
                'quantity' => 1,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'promotionCouponCode' => 'BLACKFRIDAY',
            'itemsTotal' => 9500,
            'shippingTotal' => 1000,
            'total' => 10500,
            'orderPromotionTotal' => -500, // 5% of reduction on 1000
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testItOnlyAppliesTheMostAdvantageousBlackFridayPromotion(): void
    {
        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/apply-coupon', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'couponCode' => 'BLACKFRIDAY',
            ],
        ]);

        self::assertResponseIsSuccessful();

        $this->client->request(Request::METHOD_PATCH, '/api/v2/shop/orders/5811cf75-84a3-4673-9cc3-00755d1b72as/items', [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'productVariant' => '/api/v2/shop/product-variants/TEDSO70190_TEDPBOIS15',
                'quantity' => 3, // The promotion "Blackfriday 15%" should be applied
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'promotionCouponCode' => 'BLACKFRIDAY',
            'itemsTotal' => 25500,
            'shippingTotal' => 1000,
            'total' => 26500,
            'orderPromotionTotal' => -4500, // 15% of reduction on 30000
        ]);
    }
}
