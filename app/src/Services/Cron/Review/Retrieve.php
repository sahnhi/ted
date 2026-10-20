<?php

namespace App\Services\Cron\Review;

use App\Api\TrustPilot;
use App\Doctrine\Entity\Sylius\Product;
use App\Doctrine\Repository\Sylius\ProductRepository;
use App\Doctrine\Repository\Sylius\ProductReviewRepository;
use Doctrine\ORM\EntityManagerInterface;

class Retrieve
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductRepository $productRepository,
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly TrustPilot $trustPilotApi
    ) {
    }

    /**
     * @return void
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process()
    {
        $product = $this->productRepository->findOneByCode(Product::TRUST_PILOT_REVIEW_CODE);
        $lastReview = $this->productReviewRepository->findLastForProduct($product);

        $this->trustPilotApi->retrieveReviews($product, $lastReview);

        $this->entityManager->flush();
    }
}
