<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Sylius\Component\Locale\Context\ImmutableLocaleContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ProductReviewQuestionTest extends ApiTestCase
{
    public function setUp(): void
    {
        self::bootKernel();

        self::$container->get('sylius.context.locale')
            ->addContext(new ImmutableLocaleContext('fr_FR'));
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetQuestionsForOrderToken(): void
    {
        self::createClient()->request(Request::METHOD_GET, '/api/v2/reviews/questions', [
            'query' => [
                'cartToken' => '5811cf75-84a3-4673-9cc3-69755d1b72ab',
                '_locale' => 'fr_FR',
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'hydra:member' => [
                [
                    [
                        '@type' => 'ProductReviewQuestion',
                        'question' => 'problemesAvants',
                        'product' => '/api/v2/shop/products/mattress',
                        'questionType' => 'checkbox',
                        'position' => 1,
                        'productReviewAnswerChoices' => [
                            [
                                '@type' => 'ProductReviewAnswerChoices',
                                'code' => 'backProblem',
                                'translations' => [
                                    'fr_FR' => [
                                        '@type' => 'ProductReviewAnswerChoicesTranslation',
                                        'name' => 'Mal de dos au réveil',
                                    ],
                                ],
                            ],
                        ],
                        'translations' => [
                            'fr_FR' => [
                                '@type' => 'ProductReviewQuestionTranslation',
                                'name' => 'Étiez-vous concerné(e) par un ou plusieurs de ces problèmes avant ?',
                            ],
                        ],
                    ],
                ],
            ],
            'hydra:totalItems' => 1,
        ]);
    }

    public function testItReturnsNoQuestionsForACart(): void
    {
        self::createClient()->request(Request::METHOD_GET, '/api/v2/reviews/questions', [
            'query' => [
                'cartToken' => '5811cf75-84a3-4673-9cc3-69755d1b72aa',
                '_locale' => 'fr_FR',
            ],
        ]);

        self::assertResponseIsSuccessful();

        self::assertJsonContains([
            'hydra:totalItems' => 0,
        ]);
    }
}
