<?php

declare(strict_types=1);

namespace App\Analytic\Client;

use App\Analytic\Client\Model\RimdianAuthentication;
use App\Analytic\Model\Order;
use App\Analytic\Model\User;
use App\Analytic\Model\UsersList;
use App\HttpClient\HttpClientInterface;
use App\Services\Sentry\ReportingInterface;
use Psr\Log\LoggerInterface;
use Sentry;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClient;

final class RimdianHttpClient implements AnalyticClient
{
    private const LOGIN_URL = 'https://api-eu.rimdian.com/api/account.login';
    private const IMPORT_URL = 'https://collector-eu.rimdian.com/data';

    private const IMPORT_TYPE_USER = 'user';
    private const IMPORT_TYPE_ORDER = 'order';

    private readonly Serializer $serializer;

    public function __construct(
        private readonly SymfonyHttpClient $httpClient,
        private readonly string $workspaceId,
        private readonly string $email,
        private readonly string $password,
        private readonly LoggerInterface $rimdianLogger,
        private readonly ReportingInterface $reporting
    ) {
        $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $this->serializer = new Serializer([$normalizer], [new JsonEncoder()]);
    }

    public function sendUser(User $user): void
    {
        $rimdianAuthentication = $this->authenticate();

        $body = [
            'workspace_id' => $this->workspaceId,
            'items' => [
                [
                    'kind' => self::IMPORT_TYPE_USER,
                    'user' => $user->toArray(),
                ],
            ],
        ];

        $this->postRequest($body, $rimdianAuthentication);
    }

    public function sendOrder(Order $order): void
    {
        $rimdianAuthentication = $this->authenticate();

        $body = [
            'workspace_id' => $this->workspaceId,
            'items' => [
                [
                    'kind' => self::IMPORT_TYPE_ORDER,
                    'user' => $order->userToArray(),
                    'order' => $order->orderToArray(),
                ],
            ],
        ];

        $this->postRequest($body, $rimdianAuthentication);
    }

    public function sendBatchUsers(UsersList $usersList): void
    {
        $rimdianAuthentication = $this->authenticate();

        $items = [];
        foreach ($usersList as $user) {
            $items[] = [
                'kind' => self::IMPORT_TYPE_USER,
                'user' => $user->toArray(),
            ];
        }

        $body = [
            'workspace_id' => $this->workspaceId,
            'items' => $items,
        ];

        $this->postRequest($body, $rimdianAuthentication);
    }

    private function authenticate(): RimdianAuthentication
    {
        $response = $this->httpClient->request(
            HttpClientInterface::METHOD_POST,
            self::LOGIN_URL,
            [
                'json' => [
                    'email' => $this->email,
                    'password' => $this->password,
                ],
            ]
        );

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException($response->getContent());
        }

        return $this->serializer->deserialize($response->getContent(), RimdianAuthentication::class, 'json');
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function postRequest(array $body, RimdianAuthentication $rimdianAuthentication): void
    {
        $this->rimdianLogger->info('Sylius -> Rimdian api call', [
            'payload' => $body,
        ]);

        $response = $this->httpClient->request(
            HttpClientInterface::METHOD_POST,
            self::IMPORT_URL,
            [
                'json' => $body,
                'headers' => [
                    'Authorization' => 'Bearer ' . $rimdianAuthentication->getAccessToken(),
                ],
            ]
        );

        if (399 < $response->getStatusCode()) {
            $this->rimdianLogger->error('Failed -> Rimdian import failed', [
                'payload' => $body,
                'error' => $response->getContent(),
            ]);

            $this->reporting->reportIssue(
                $response->getContent(),
                Sentry\Severity::error(),
                ['rimdian' => self::IMPORT_URL],
                ['payload' => $body]
            );
        }
    }
}
