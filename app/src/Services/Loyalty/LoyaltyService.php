<?php

namespace App\Services\Loyalty;

use App\Doctrine\Entity\Sylius\PromotionInterface;
use App\Doctrine\Entity\Sylius\PromotionRuleInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class LoyaltyService implements LoyaltyServiceInterface
{
    public function __construct(
        private readonly RepositoryInterface $customerRepository,
        private readonly RepositoryInterface $orderRepository,
        private readonly RepositoryInterface $promotionCouponRepository,
        private readonly RepositoryInterface $customerGroupRepository
    ) {
    }

    public function verifyCustomerEligibilityByEmailAndCoupon(string $email, string $coupon)
    {
        if ($this->customerRepository->findOneByEmail($email)
            && $this->orderRepository->getOrderByCustomerEmail($email)) {
            /** @var PromotionCouponInterface $promotionCoupon */
            $promotionCoupon = $this->promotionCouponRepository->findOneByCode($coupon);

            if ($promotionCoupon instanceof PromotionCouponInterface) {
                foreach ($promotionCoupon->getPromotion()->getRules() as $promotionRule) {
                    if (PromotionRuleInterface::TYPE_SPONSORING === $promotionRule->getType() && !empty($promotionRule->getConfiguration()['nth'])) {
                        return ['coupon_type' => 'gift'];
                    }
                }
            }

            return false;
        }

        return true;
    }

    public function verifyCouponIsValid(string $coupon)
    {
        /* @var PromotionCouponInterface $promotionCoupon */
        if (!$this->promotionCouponRepository->findOneByCodeAndPromotionCode($coupon, PromotionInterface::PROMOTION_SPONSOR_CODE)
            || !$this->customerRepository->findOneByPromotionCoupon($coupon)) {
            return false;
        }

        return $this->promotionCouponRepository->findOneByCode($coupon);
    }

    public function verifyGroupExistByCode(string $groupCode)
    {
        if (!$group = $this->customerGroupRepository->findOneByCode($groupCode)) {
            return false;
        }

        return $group;
    }
}
