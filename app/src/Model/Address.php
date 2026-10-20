<?php

namespace App\Model;

final class Address
{
    private function __construct(
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly string $city,
        private readonly string $street,
        private readonly string $countryCode,
        private readonly string $postcode,
        private readonly ?string $provinceName = null,
        private readonly ?string $provinceCode = null,
        private readonly ?string $phoneNumber = null,
        private readonly ?string $company = null,
        private readonly ?string $details = null,
        private readonly ?string $tva = null,
    ) {
    }

    public static function createFromArray(array $address, bool $fullData = true): self
    {
        if (!$fullData) {
            return new self(
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            );
        }

        return new self(
            isset($address['fullName']) ? '' : $address['firstName'],
            $address['fullName'] ?? $address['lastName'],
            $address['city'],
            $address['street'],
            $address['countryCode'],
            $address['postcode'],
            $address['provinceName'] ?? null,
            $address['provinceCode'] ?? null,
            $address['phoneNumber'] ?? null,
            $address['company'] ?? null,
            $address['details'] ?? null,
            $address['tva'] ?? null,
        );
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }

    public function getProvinceName(): ?string
    {
        return $this->provinceName;
    }

    public function getProvinceCode(): ?string
    {
        return $this->provinceCode;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
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
