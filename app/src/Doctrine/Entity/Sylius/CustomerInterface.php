<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\Familiarity;
use App\Doctrine\Entity\Friend;
use App\Doctrine\Entity\Osteopath;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\CustomerInterface as BaseCustomerInterface;

interface CustomerInterface extends BaseCustomerInterface
{
    public const TAG_FILLEUL = 'filleul';

    public function getPaymentMethod(): ?PaymentMethod;

    public function setPaymentMethod(?PaymentMethod $paymentMethod = null): void;

    public function getLocale(): ?string;

    public function setLocale(string $locale): void;

    public function getFriend(): ?Friend;

    public function setFriend(?Friend $friend = null): void;

    public function getOsteopath(): ?Osteopath;

    public function setOsteopath(?Osteopath $osteopath = null): void;

    public function getReferral(): ?string;

    public function setReferral(string $referral): void;

    public function isOptin(): ?bool;

    public function setOptin(bool $optin): void;

    public function getFamiliarities(): ?Collection;

    public function hasFamiliarity(Familiarity $familiarity): bool;

    public function setFamiliarities(Collection $familiarities): Customer;

    public function addFamiliarity(Familiarity $familiarity): void;

    public function removeFamiliarity(Familiarity $familiarity): void;

    public function setForceLocale();

    public function getOptin();

    public function setNbFilleul(int $nbFilleul): void;

    public function getNbFilleul(): ?int;

    public function setParrainOrderAmountTotal(int $parrainOrderAmountTotal): void;

    public function getParrainOrderAmountTotal(): ?int;

    public function setSponsor(?Customer $sponsor = null): void;

    public function getSponsor(): ?Customer;

    public function isSponsor(): bool;

    public function getPromotionCoupons(): ?Collection;

    public function hasPromotionCoupon(PromotionCoupon $promotionCoupon): bool;

    public function setPromotionCoupons(Collection $promotionCoupons): void;

    public function addPromotionCoupon(PromotionCoupon $promotionCoupon): void;

    public function removePromotion(PromotionCoupon $promotionCoupon): void;

    public function setOptinPhone(bool $optinPhone): void;

    public function isOptinPhone(): bool;
}
