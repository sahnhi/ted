<?php

namespace App\Message\DTO;

use App\Doctrine\Entity\Familiarity;
use App\Doctrine\Entity\Friend;
use App\Doctrine\Entity\Osteopath;
use App\Doctrine\Entity\Sylius\Customer;
use App\Doctrine\Entity\Sylius\PaymentMethod;
use App\Doctrine\Entity\Sylius\PromotionCoupon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Customer\Model\CustomerGroupInterface;
use Sylius\Component\Customer\Model\CustomerInterface;

final class CustomerDTO extends ArrayCollection
{
    public $id;

    /** @var string|null */
    public $email;

    /** @var string|null */
    public $emailCanonical;

    /** @var string|null */
    public $firstName;

    /** @var string|null */
    public $lastName;

    /** @var string|null */
    private $fullName;

    /** @var \DateTimeInterface|null */
    public $birthday;

    /** @var string */
    public $gender = CustomerInterface::UNKNOWN_GENDER;

    /** @var CustomerGroupInterface|null */
    public $group;

    /** @var string|null */
    public $phoneNumber;

    /** @var bool */
    public $subscribedToNewsletter = false;

    /** @var Collection|OrderInterface[] */
    public $orders;

    /** @var AddressInterface */
    public $defaultAddress;

    /** @var Collection|AddressInterface[] */
    public $addresses;

    /** @var ShopUserInterface */
    public $user;

    /** @var PaymentMethod */
    public $paymentMethod;

    /** @var string */
    public $locale;

    /** @var Familiarity */
    public $familiarities;

    /** @var Osteopath */
    public $osteopath;

    /** @var Friend */
    public $friend;

    /** @var string */
    public $referral;

    /** @var bool */
    public $optin;

    /** @var int */
    public $nbFilleul;

    /** @var int */
    public $parrainOrderAmountTotal;

    /** @var string */
    public $sponsorCoupon;

    /** @var Customer */
    public $sponsor;

    /** @var PromotionCoupon */
    public $promotionCoupons;

    public function __construct(Customer $customer)
    {
        $this->id = $customer->getId();
        $this->email = $customer->getEmail();
        $this->emailCanonical = $customer->getEmailCanonical();
        $this->firstName = $customer->getFirstName();
        $this->lastName = $customer->getLastName();
        $this->fullName = $customer->getFullName();
        $this->birthday = $customer->getBirthday();
        $this->gender = $customer->getGender();
        $this->group = $customer->getGroup();
        $this->phoneNumber = $customer->getPhoneNumber();
        $this->subscribedToNewsletter = $customer->isSubscribedToNewsletter();
        $this->orders = $customer->getOrders();
        $this->defaultAddress = $customer->getDefaultAddress();
        $this->addresses = $customer->getAddresses();
        $this->user = $customer->getUser();
        $this->paymentMethod = $customer->getPaymentMethod();
        $this->locale = $customer->getLocale();
        $this->familiarities = $customer->getFamiliarities();
        $this->osteopath = $customer->getOsteopath();
        $this->friend = $customer->getFriend();
        $this->referral = $customer->getReferral();
        $this->optin = $customer->getOptin();
        $this->nbFilleul = $customer->getNbFilleul();
        $this->parrainOrderAmountTotal = $customer->getParrainOrderAmountTotal();
        $this->sponsorCoupon = $customer->getSponsorCoupon();
        $this->sponsor = $customer->getSponsor();
        $this->promotionCoupons = $customer->getPromotionCoupons();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getEmailCanonical(): ?string
    {
        return $this->emailCanonical;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getGroup(): ?CustomerGroupInterface
    {
        return $this->group;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function isSubscribedToNewsletter(): bool
    {
        return $this->subscribedToNewsletter;
    }

    /**
     * @return Collection|OrderInterface[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    public function getDefaultAddress(): ?AddressInterface
    {
        return $this->defaultAddress;
    }

    /**
     * @return Collection|AddressInterface[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @return ShopUserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @return Familiarity
     */
    public function getFamiliarities()
    {
        return $this->familiarities;
    }

    public function getOsteopath(): ?Osteopath
    {
        return $this->osteopath;
    }

    public function getFriend(): ?Friend
    {
        return $this->friend;
    }

    public function getReferral(): ?string
    {
        return $this->referral;
    }

    public function isOptin(): ?bool
    {
        return $this->optin;
    }

    public function getNbFilleul(): ?int
    {
        return $this->nbFilleul;
    }

    public function getParrainOrderAmountTotal(): ?int
    {
        return $this->parrainOrderAmountTotal;
    }

    public function getSponsorCoupon(): ?string
    {
        return $this->sponsorCoupon;
    }

    public function getSponsor(): ?Customer
    {
        return $this->sponsor;
    }

    /**
     * @return PromotionCoupon
     */
    public function getPromotionCoupons()
    {
        return $this->promotionCoupons;
    }
}
