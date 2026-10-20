<?php

namespace App\Services\Payment;

use App\Api\AdyenPayByLink\Client\AdyenPayByLinkClient;
use App\Api\AdyenPayByLink\Query\AdyenPayByLinkQueryFactory;
use App\Doctrine\Entity\Sylius\OrderInterface;
use App\Doctrine\Repository\Sylius\OrderRepositoryInterface;
use App\Message\DTO\OrderDTO;
use App\Services\Mail\MailerEmail;
use App\Services\Mail\PayByLinkEmail;
use Webmozart\Assert\Assert;

class SendAdyenPaymentLink
{
    public function __construct(
        private readonly AdyenPayByLinkQueryFactory $adyenPayByLinkQueryFactory,
        private readonly AdyenPayByLinkClient $adyenPayByLinkClient,
        private readonly MailerEmail $mailer,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function send(string $orderId, string $paymentReference, string $merchantAccount, string $apiUrl, string $apiKey): void
    {
        $order = $this->orderRepository->find((int) $orderId);
        Assert::isInstanceOf($order, OrderInterface::class);

        $link = $this->getLink($order, $paymentReference, $merchantAccount, $apiUrl, $apiKey);

        $this->mailer->sendEmail(PayByLinkEmail::class, new OrderDTO($order), ['link' => $link]);
    }

    private function getLink(OrderInterface $order, string $paymentReference, string $merchantAccount, string $apiUrl, string $apiKey): string
    {
        $query = $this->adyenPayByLinkQueryFactory->create($order, $paymentReference, $merchantAccount);
        $content = $this->adyenPayByLinkClient->createLink($query, $apiUrl, $apiKey);

        Assert::isArray($content);
        Assert::keyExists($content, 'url');

        return $content['url'];
    }
}
