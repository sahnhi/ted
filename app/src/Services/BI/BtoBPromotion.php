<?php

namespace App\Services\BI;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class BtoBPromotion
{
    public function __construct(
        private readonly RepositoryInterface $channelRepository
    ) {
    }

    public function check(OrderInterface $object)
    {
        /** @var PromotionCouponInterface $coupon */
        $coupon = $object->getPromotionCoupon();
        $promotion = null !== $coupon ? $coupon->getPromotion() : null;

        if (null !== $promotion && str_starts_with((string) $promotion->getCode(), 'b2b-channel')) {
            $channel = $this->channelRepository->findOneByCode('b2b');
            $object->setChannel($channel);
        }
    }
}
