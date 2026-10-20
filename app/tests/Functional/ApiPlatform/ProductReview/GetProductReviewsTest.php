<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform\ProductReview;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Sylius\Component\Locale\Context\ImmutableLocaleContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GetProductReviewsTest extends ApiTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     *
     * @dataProvider provideDataByLocale
     */
    public function testItGetsTheReviewOnlyForTheCurrentLocale(string $locale, int $expectedCount, array $expectedBody): void
    {
        $client = self::createClient();

        self::$container->get('sylius.context.locale')
            ->addContext(new ImmutableLocaleContext($locale));

        $client->request(Request::METHOD_GET, '/api/v2/shop/product-reviews');

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'hydra:member' => [$expectedBody],
            'hydra:totalItems' => $expectedCount,
        ]);
    }

    private function provideDataByLocale(): \Generator
    {
        yield [
            'fr_FR',
            1,
            [
                '@type' => 'ProductReview',
                'answers' => [],
                'title' => 'Comment est votre blanquette ?',
                'rating' => 5,
                'comment' => 'La blanquette est bonne.',
                'author' => [
                    '@id' => '/api/v2/shop/customers/1',
                    '@type' => 'Customer',
                    'firstName' => 'customer1 firstname',
                    'lastName' => 'customer1 lastname',
                ],
                'status' => 'accepted',
                'reviewSubject' => '/api/v2/shop/products/mattress',
            ],
        ];

        yield [
            'es_ES',
            1,
            [
                '@type' => 'ProductReview',
                'answers' => [],
                'title' => 'On me dit le plus grand bien des harengs pommes à l\'huile.',
                'rating' => 2,
                'comment' => 'Je vous en sers un ramequin, vous vous en ferez une idée.',
                'author' => [
                    '@id' => '/api/v2/shop/customers/1',
                    '@type' => 'Customer',
                    'firstName' => 'customer1 firstname',
                    'lastName' => 'customer1 lastname',
                ],
                'status' => 'accepted',
                'reviewSubject' => '/api/v2/shop/products/mattress',
            ],
        ];
    }
}
