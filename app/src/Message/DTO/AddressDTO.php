<?php

namespace App\Message\DTO;

use App\Doctrine\Entity\Sylius\Address;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\CustomerInterface;

final class AddressDTO extends ArrayCollection
{
    public $id;

    /** @var string|null */
    public $firstName;

    /** @var string|null */
    public $lastName;

    /** @var string|null */
    private $fullName;

    /** @var string|null */
    public $phoneNumber;

    /** @var string|null */
    public $company;

    /** @var string|null */
    public $countryCode;

    /** @var string|null */
    public $provinceCode;

    /** @var string|null */
    public $provinceName;

    /** @var string|null */
    public $street;

    /** @var string|null */
    public $city;

    /** @var string|null */
    public $postcode;

    /** @var CustomerInterface|null */
    public $customer;

    /** @var string */
    public $details;

    /** @var string */
    public $tva;

    public function __construct(Address $address)
    {
        $this->id = $address->getId();
        $this->firstName = $address->getFirstName();
        $this->lastName = $address->getLastName();
        $this->fullName = $address->getFullName();
        $this->phoneNumber = $address->getPhoneNumber();
        $this->company = $address->getCompany();
        $this->countryCode = $address->getCountryCode();
        $this->provinceCode = $address->getProvinceCode();
        $this->provinceName = $address->getProvinceName();
        $this->street = $address->getStreet();
        $this->city = $address->getCity();
        $this->postcode = $address->getPostcode();

        $this->customer = null;
        if ($address->getCustomer()) {
            $this->customer = new CustomerDTO($address->getCustomer());
        }

        $this->details = $address->getDetails();
        $this->tva = $address->getTva();
    }

    public function getId()
    {
        return $this->id;
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

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function getProvinceCode(): ?string
    {
        return $this->provinceCode;
    }

    public function getProvinceName(): ?string
    {
        return $this->provinceName;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    /**
     * @return CustomerInterface|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function getTva(): ?string
    {
        return $this->tva;
    }
}
