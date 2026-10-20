<?php

namespace App\Services\Drip;

use App\Doctrine\Entity\Sylius\Customer;
use App\Services\Sentry\ReportingInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

interface DripApiInterface
{
    public function __construct(
        string $dripAccountId,
        MessageBusInterface $bus,
        ReportingInterface $reporting,
        HttpClientInterface $dripHttpClient
    );

    public function sendOrder(OrderInterface $order, array $customFields = []): void;

    public function sendBatchOrders(array $orders): void;

    public function sendCustomer(Customer $customer, array $tags = [], array $customFields = []): void;

    public function sendBatchCustomers(array $customers): void;

    public function sendTag(string $email, array $tags): void;

    public function request(string $apiUrl, array $post): string;

    public function sendBatchProducts(array $variants, ChannelInterface $channel): string;

    public function constructProductsCreateBody(array $variants, ChannelInterface $channel): array;

    public function sendEvent(array $events): void;
}
