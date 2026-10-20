<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\ProductReview;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AddProductReviewTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testPost(): void
    {
        self::createClient()->request(Request::METHOD_POST, '/api/v2/shop/product-reviews', [
            'json' => [
                'firstName' => 'Bruce',
                'lastName' => 'Batman',
                'email' => 'bruce.wayne@notbatman.com',
                'product' => '/api/v2/shop/products/mattress',
                'rating' => 5,
                'title' => 'Alfred loves it!',
                'comment' => 'Awesome mattress!',
                'cartToken' => '5811cf75-84a3-4673-9cc3-69755d1b72aa',
                'answers' => [
                    'problemesAvants' => ['backProblem'],
                ],
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        self::assertJsonContains([
            'rating' => 5,
            'title' => 'Alfred loves it!',
            'comment' => 'Awesome mattress!',
            'reviewSubject' => '/api/v2/shop/products/mattress',
            'answers' => [
                [
                    'answer' => 'backProblem',
                    'answerChoices' => [
                        'code' => 'backProblem',
                        'translations' => [
                            'fr_FR' => [
                                'name' => 'Mal de dos au réveil',
                            ],
                        ],
                    ],
                ],
            ],
            'author' => [
                'firstName' => 'Bruce',
                'lastName' => 'Batman',
            ],
        ]);
    }
}
