<?php

namespace App\Doctrine\Repository\Sylius;

use Sylius\Bundle\PromotionBundle\Doctrine\ORM\PromotionCouponRepository as BasePromotionCouponRepository;

class PromotionCouponRepository extends BasePromotionCouponRepository
{
    public function csvListQueryBuilder($promotionId = null, ?array $criteria = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.promotion = :promotionId')
            ->setParameter('promotionId', $promotionId);

        if (isset($criteria)) {
            if (isset($criteria['usageUsed'])) {
                $qb->andWhere('o.used = :usageUsed')
                    ->setParameter('usageUsed', $criteria['usageUsed']['value']);
            }

            if (isset($criteria['usageLimit'])) {
                $qb->andWhere('o.usageLimit = :usageLimit')
                    ->setParameter('usageLimit', $criteria['usageLimit']['value']);
            }

            if (isset($criteria['existExpiresDate'])) {
                if (1 === $criteria['existExpiresDate']) {
                    $qb->andWhere('o.expiresAt IS NULL');
                } else {
                    $qb->andWhere('o.expiresAt IS NOT NULL');
                }
            }
        }

        return $qb->getQuery()->iterate();
    }
}
