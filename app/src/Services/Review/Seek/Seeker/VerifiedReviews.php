<?php

namespace App\Services\Review\Seek\Seeker;

use App\Api\VerifiedReviews as VerifiedReviewsApi;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Services\Review\Seek\AbstractSeeker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class VerifiedReviews extends AbstractSeeker
{
    public const NAME = 'VerifiedReviews';

    public function __construct(
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        private readonly VerifiedReviewsApi $verifiedReviewsApi
    ) {
        parent::__construct($entityManager, $orderRepository);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function seekReview(Order $order): void
    {
        $this->verifiedReviewsApi->sendOrder($order);
    }
}
