<?php

namespace App\Api;

use App\Api\VerifiedReviews\Invitation;
use App\Doctrine\Entity\Sylius\Order;
use App\HttpClient\HttpClientInterface;
use App\Services\Sentry\ReportingInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Sentry;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as BaseHttpClientInterface;

class VerifiedReviews
{
    public const API_SEEK_REVIEW_URL = 'https://www.avis-verifies.com/index.php?action=act_api_notification_sha1&type=json2';

    private readonly Serializer $serializer;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BaseHttpClientInterface $client,
        private readonly string $apiKey,
        private readonly string $apiSecret,
        private readonly ReportingInterface $reporting
    ) {
        $this->serializer = SerializerBuilder::create()
            ->setPropertyNamingStrategy(new IdenticalPropertyNamingStrategy())
            ->build();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function sendOrder(Order $order): void
    {
        try {
            $invitation = new Invitation($order, $this->apiKey, $this->apiSecret);
            $payload = $this->serializer->toArray($invitation);
            $payload['message'] = json_encode($payload['message']);

            $response = $this->client->request(
                HttpClientInterface::METHOD_POST,
                self::API_SEEK_REVIEW_URL,
                ['body' => $payload]
            );

            $success = 1 === json_decode($response->getContent())->return;

            if ($success) {
                $order->setReviewType(Order::REVIEW_TYPE_VERIFIED_REVIEWS)
                    ->setReviewEmailSent(true);
                $this->entityManager->persist($order);
            }
        } catch (\Exception $e) {
            $this->reporting->reportIssue(
                $e,
                Sentry\Severity::warning(),
                [
                    'service' => 'verified-review',
                    'order' => $order,
                ]
            );
        }
    }
}
