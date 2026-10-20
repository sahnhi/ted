<?php

declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Doctrine\Entity\Counter;
use Sylius\Component\Locale\Context\ImmutableLocaleContext;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CounterTest extends ApiTestCase
{
    public function setUp(): void
    {
        self::bootKernel();

        self::$container->get('sylius.context.locale')
            ->addContext(new ImmutableLocaleContext('fr_FR'));
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/api/v2/counters');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/v2/contexts/Counter',
            '@id' => '/api/v2/counters',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
        ]);

        $this->assertCount(1, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Counter::class);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetCounter(): void
    {
        $response = static::createClient()->request('GET', '/api/v2/counters/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertMatchesResourceItemJsonSchema(Counter::class);

        $this->assertJsonContains([
            '@context' => '/api/v2/contexts/Counter',
            '@id' => '/api/v2/counters/1',
            '@type' => 'Counter',
            'id' => 1,
            'key' => 'test',
            'year' => 2024,
            'counter' => 10,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testPutCounter(): void
    {
        $response = static::createClient()->request('PUT', '/api/v2/counters/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertMatchesResourceItemJsonSchema(Counter::class);

        $this->assertJsonContains([
            '@context' => '/api/v2/contexts/Counter',
            '@id' => '/api/v2/counters/1',
            '@type' => 'Counter',
            'id' => 1,
            'key' => 'test',
            'year' => 2024,
            'counter' => 11,
        ]);
    }
}
