<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Functional\Helper\FixtureLoader;
use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->disableReboot();
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        $connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        /** @var Connection $connection */
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }
    }

    public function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    public function loadFixture(): void
    {
        $fixtureLoader = $this->getContainer()->get(FixtureLoader::class);
        $fixtureLoader->load();
    }

    public function get(string $uri, array $headers = [], array $server = []): Response
    {
        return $this->request('GET', $uri, [], $headers, $server);
    }

    public function post(string $uri, array $content = [], array $headers = [], array $server = []): Response
    {
        return $this->request('POST', $uri, $content, $headers, $server);
    }

    public function delete(string $uri, array $content = [], array $headers = [], array $server = []): Response
    {
        return $this->request('DELETE', $uri, $content, $headers, $server);
    }

    public function put(string $uri, array $content = [], array $headers = [], array $server = []): Response
    {
        return $this->request('PUT', $uri, $content, $headers, $server);
    }

    private function request(string $method, string $uri, array $content = [], array $headers = [], array $server = [], array $files = []): Response
    {
        foreach ($headers as $header => $value) {
            $server['HTTP_' . strtoupper($header)] = $value;
        }
        $crawler = $this->client->request($method, $uri, $content, $files, $server, json_encode($content));

        return new Response($this->client, $crawler);
    }
}
