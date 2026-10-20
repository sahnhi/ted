<?php

namespace App\Services\Review\Seek\Seeker;

use App\Api\TrustPilot as TrustPilotApi;
use App\Doctrine\Entity\Sylius\Order;
use App\Doctrine\Repository\Sylius\OrderRepository;
use App\Services\Review\Seek\AbstractSeeker;
use App\Utils\DateRange;
use Doctrine\ORM\EntityManagerInterface;

class TrustPilot extends AbstractSeeker
{
    public const NAME = 'TrustPilot';

    public function __construct(
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        private readonly TrustPilotApi $trustPilotApi
    ) {
        parent::__construct($entityManager, $orderRepository);
    }

    protected function initSeeker(DateRange $dateRange): void
    {
        parent::initSeeker($dateRange);
        $this->trustPilotApi->setAskReviewTemplate($this->getAskReviewTemplate());
    }

    protected function seekReview(Order $order): void
    {
        $this->trustPilotApi->sendOrder($order);
    }
}
