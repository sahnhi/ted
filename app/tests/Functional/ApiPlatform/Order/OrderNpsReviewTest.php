<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\Order;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class OrderNpsReviewTest extends ApiTestCase
{
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testAddNpsReviewOnOrder(): void
    {
        $tokenValue = '042f6288-1e61-48f9-aedc-3452463d0742';

        static::createClient()->request(
            'PATCH', "/api/v2/shop/orders/{$tokenValue}/nps-review",
            ['body' => json_encode(['NPS' => 5],
                JSON_THROW_ON_ERROR
            ),
             'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/merge-patch+json']]
        );
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');

        self::assertJsonContains([
            'tokenValue' => '042f6288-1e61-48f9-aedc-3452463d0742',
            'itemsTotal' => 6900,
        ]);
    }

    public function testAddNpsReviewOnOrderAlreadyReviewed(): void
    {
        $tokenValue = '042f6288-1e61-48f9-aedc-3452463d0799';

        static::createClient()->request(
            'PATCH',
            "/api/v2/shop/orders/{$tokenValue}/nps-review",
            [
                'body' => json_encode(['NPS' => 5], JSON_THROW_ON_ERROR),
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/merge-patch+json'],
            ]
        );
        self::assertResponseStatusCodeSame(422);

        self::assertJsonContains([
            'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
            'title' => 'An error occurred',
            'detail' => 'NPS: The order nps review is already set.',
            'violations' => [
                    0 => [
                            'propertyPath' => 'NPS',
                            'message' => 'The order nps review is already set.',
                            'code' => null,
                        ],
                ],
        ]);
    }
}
