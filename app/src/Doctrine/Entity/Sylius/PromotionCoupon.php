<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Repository\Sylius\PromotionCouponRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PromotionCoupon as BasePromotionCoupon;

#[ORM\Table(name: 'sylius_promotion_coupon')]
#[ORM\Entity(repositoryClass: PromotionCouponRepository::class)]
class PromotionCoupon extends BasePromotionCoupon
{
}
