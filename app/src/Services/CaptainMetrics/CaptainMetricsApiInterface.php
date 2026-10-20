<?php

namespace App\Services\CaptainMetrics;

use App\Doctrine\Entity\OrderStore;
use App\Doctrine\Entity\Sylius\Order;
use App\Services\Sentry\ReportingInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

interface CaptainMetricsApiInterface
{
    public const CAPTAIN_METRICS_URL = 'https://photon.captainmetrics.com/data.import';

    public function __construct(
        string $projectId,
        string $accountToken,
        MessageBusInterface $bus,
        RepositoryInterface $orderRepository,
        ReportingInterface $reporting
    );

    public function request(array $payload, string $tag): string;

    public function sendBatchUsers(array $users, string $channel = 'default'): string;

    public function sendBatchOrders(array $orders): string;

    public function sendUser(Order $order): void;

    public function sendOrderStore(OrderStore $orderStore): void;
}
