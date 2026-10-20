<?php

namespace App\Services\Review\Seek;

use App\Doctrine\Entity\AskReviewTemplate;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Utils\DateRange;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractSeeker implements SeekerInterface
{
    private AskReviewTemplate $askReviewTemplate;

    protected array $orders;
    protected int $limit;

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function getOrderRepository(): OrderRepository
    {
        return $this->orderRepository;
    }

    public function getAskReviewTemplate(): AskReviewTemplate
    {
        return $this->askReviewTemplate;
    }

    public function setAskReviewTemplate(AskReviewTemplate $askReviewTemplate): AbstractSeeker
    {
        $this->askReviewTemplate = $askReviewTemplate;

        return $this;
    }

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository
    ) {
    }

    public function seekReviews(DateRange $dateRange, int $total): void
    {
        $this->initSeeker($dateRange);

        shuffle($this->orders);
        $limit = (int) round(
            $this->getAskReviewTemplate()->getPercentage() * $total / 100,
            null,
            PHP_ROUND_HALF_DOWN
        );

        foreach ($this->orders as $i => $order) {
            if ($i >= $limit) {
                break;
            }
            $this->seekReview($order);
        }

        $this->getEntityManager()->flush();
    }

    protected function initSeeker(DateRange $dateRange): void
    {
        $this->orders = $this->getOrderRepository()->findAllForThirdPartyReviews($dateRange);
    }

    abstract protected function seekReview(Order $order): void;
}
