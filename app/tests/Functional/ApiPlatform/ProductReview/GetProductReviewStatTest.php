<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\ProductReview;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GetProductReviewStatTest extends ApiTestCase
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGet(): void
    {
        self::createClient()->request(Request::METHOD_GET, '/api/v2/shop/product-reviews/stats', [
            'query' => [
                'reviewSubject' => 'mattress',
                'page' => 1,
                'itemPerPage' => 30,
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            '@context' => '/api/v2/contexts/ProductReview',
            '@id' => '/api/v2/shop/product-reviews',
            '@type' => 'hydra:Collection',
            'hydra:member' => [
                [
                    '@type' => 'ProductReviewStatsSummary',
                    'productCode' => 'mattress',
                    'totalReviews' => 3,
                    'averageRating' => 3,
                    'ratingDistribution' => [
                        '1' => 0,
                        '2' => 2,
                        '3' => 0,
                        '4' => 0,
                        '5' => 1,
                    ],
                ],
            ],
            'hydra:totalItems' => 1,
        ]);
    }
}
