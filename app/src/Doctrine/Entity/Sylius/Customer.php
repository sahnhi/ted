<?php

namespace App\Doctrine\Entity\Sylius;

use App\Doctrine\Entity\Familiarity;
use App\Doctrine\Entity\Friend;
use App\Doctrine\Entity\Osteopath;
use App\Doctrine\Repository\Sylius\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Customer as BaseCustomer;
use Sylius\Component\Core\Model\PromotionCouponInterface;

#[ORM\Table(name: 'sylius_customer')]
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Customer extends BaseCustomer implements CustomerInterface
{
    private ?int $nbFilleul = 0;
    private ?int $parrainOrderAmountTotal = null;
    private ?string $sponsorCoupon = null;
    #[ORM\Column(name: 'locale', type: 'string', length: 2)]
    private ?string $locale = null;

    #[ORM\Column(name: 'referral', type: 'string', length: 100, nullable: true)]
    private ?string $referral = null;

    #[ORM\Column(name: 'optin', type: 'boolean', nullable: true)]
    private ?bool $optin = null;

    #[ORM\Column(name: 'optin_phone', type: 'boolean', nullable: true)]
    private ?bool $optinPhone = false;

    #[ORM\JoinColumn(name: 'payment_method_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: PaymentMethod::class)]
    private ?PaymentMethod $paymentMethod = null;

    #[ORM\JoinColumn(name: 'osteopath_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Osteopath::class, cascade: ['persist'])]
    private ?Osteopath $osteopath = null;

    #[ORM\JoinColumn(name: 'friend_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Friend::class, cascade: ['persist'])]
    private ?Friend $friend = null;

    #[ORM\JoinColumn(name: 'sponsor_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Customer::class)]
    private ?Customer $sponsor = null;

    #[ORM\JoinTable(name: 'sylius_customers_familiarities')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'familiarity_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Familiarity::class)]
    private ?Collection $familiarities;

    #[ORM\JoinTable(name: 'sylius_customers_promotion_coupons')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'promotion_coupon_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: PromotionCoupon::class)]
    private ?Collection $promotionCoupons;

    public function __construct()
    {
        parent::__construct();

        $this->familiarities = new ArrayCollection();
        $this->promotionCoupons = new ArrayCollection();
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod = null): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getFriend(): ?Friend
    {
        return $this->friend;
    }

    public function setFriend(?Friend $friend = null): void
    {
        $this->friend = $friend;
    }

    public function getOsteopath(): ?Osteopath
    {
        return $this->osteopath;
    }

    public function setOsteopath(?Osteopath $osteopath = null): void
    {
        $this->osteopath = $osteopath;
    }

    public function getReferral(): ?string
    {
        return $this->referral;
    }

    public function setReferral($referral): void
    {
        $this->referral = $referral;
    }

    public function getFamiliarities(): ?Collection
    {
        return $this->familiarities;
    }

    public function setFamiliarities(Collection $familiarities): Customer
    {
        $this->familiarities = $familiarities;

        return $this;
    }

    public function addFamiliarity(Familiarity $familiarity): void
    {
        if (!$this->hasFamiliarity($familiarity)) {
            $this->familiarities->add($familiarity);
        }
    }

    public function hasFamiliarity(Familiarity $familiarity): bool
    {
        return $this->familiarities->contains($familiarity);
    }

    public function removeFamiliarity(Familiarity $familiarity): void
    {
        if ($this->familiarities->contains($familiarity)) {
            $this->familiarities->removeElement($familiarity);
        }
    }

    #[ORM\PrePersist]
    public function setForceLocale(): void
    {
        if (!$this->getLocale()) {
            $this->setLocale('fr_FR');
        }
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = substr($locale, 0, 2);
    }

    public function getOptin(): ?bool
    {
        return $this->optin;
    }

    public function isOptin(): ?bool
    {
        return $this->optin;
    }

    public function setOptin($optin): void
    {
        $this->optin = $optin;
    }

    public function getNbFilleul(): ?int
    {
        return $this->nbFilleul;
    }

    public function setNbFilleul(int $nbFilleul): void
    {
        $this->nbFilleul = $nbFilleul;
    }

    public function getParrainOrderAmountTotal(): ?int
    {
        return $this->parrainOrderAmountTotal;
    }

    public function setParrainOrderAmountTotal(int $parrainOrderAmountTotal): void
    {
        $this->parrainOrderAmountTotal = $parrainOrderAmountTotal;
    }

    public function getSponsorCoupon(): ?string
    {
        return $this->sponsorCoupon;
    }

    public function setSponsorCoupon(string $sponsorCoupon): void
    {
        $this->sponsorCoupon = $sponsorCoupon;
    }

    public function getSponsor(): ?Customer
    {
        return $this->sponsor;
    }

    public function setSponsor(?Customer $sponsor = null): void
    {
        $this->sponsor = $sponsor;
    }

    public function isSponsor(): bool
    {
        return null !== $this->sponsor;
    }

    public function getPromotionCoupons(): ?Collection
    {
        return $this->promotionCoupons->filter(fn (PromotionCouponInterface $promotionCoupon): bool => $promotionCoupon->getExpiresAt() >= new \DateTime());
    }

    public function setPromotionCoupons(Collection $promotionCoupons): void
    {
        $this->promotionCoupons = $promotionCoupons;
    }

    public function getAllPromotionCoupons(): ?Collection
    {
        return $this->promotionCoupons;
    }

    public function addPromotionCoupon(PromotionCoupon $promotionCoupon): void
    {
        if (!$this->hasPromotionCoupon($promotionCoupon)) {
            $this->promotionCoupons[] = $promotionCoupon;
        }
    }

    public function hasPromotionCoupon(PromotionCoupon $promotionCoupon): bool
    {
        return $this->promotionCoupons->contains($promotionCoupon);
    }

    public function removePromotion(PromotionCoupon $promotionCoupon): void
    {
        if ($this->promotionCoupons->contains($promotionCoupon)) {
            $this->promotionCoupons->removeElement($promotionCoupon);
        }
    }

    public function isOptinPhone(): bool
    {
        return (bool) $this->optinPhone;
    }

    public function setOptinPhone(bool $optinPhone): void
    {
        $this->optinPhone = $optinPhone;
    }
}
