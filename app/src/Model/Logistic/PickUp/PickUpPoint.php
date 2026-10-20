<?php

declare(strict_types=1);

namespace App\Model\Logistic\PickUp;

class PickUpPoint
{
    private string $id;
    private bool $active;
    private string $distance;
    private string $name;
    private string $address;
    private string $addressBis;
    private string $addressTer;
    private string $addressQuarter;
    private string $localHint;
    private string $zipCode;
    private string $city;
    private string $countryCode;
    private float $longitude;
    private float $latitude;
    private string $mapUrl;
    private string $available;
    private array $openingHours = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDistance(): string
    {
        return $this->distance;
    }

    public function setDistance(string $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = trim($address);

        return $this;
    }

    public function getAddressBis(): string
    {
        return $this->addressBis;
    }

    public function setAddressBis(string $addressBis): self
    {
        $this->addressBis = trim($addressBis);

        return $this;
    }

    public function getAddressTer(): string
    {
        return $this->addressTer;
    }

    public function setAddressTer(string $addressTer): self
    {
        $this->addressTer = trim($addressTer);

        return $this;
    }

    public function getAddressQuarter(): string
    {
        return $this->addressQuarter;
    }

    public function setAddressQuarter(string $addressQuarter): self
    {
        $this->addressQuarter = trim($addressQuarter);

        return $this;
    }

    public function getLocalHint(): string
    {
        return $this->localHint;
    }

    public function setLocalHint(string $localHint): self
    {
        $this->localHint = trim($localHint);

        return $this;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getMapUrl(): string
    {
        return $this->mapUrl;
    }

    public function setMapUrl(string $mapUrl): self
    {
        $this->mapUrl = trim($mapUrl);

        return $this;
    }

    public function getAvailable(): string
    {
        return $this->available;
    }

    public function setAvailable(string $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getOpeningHours(): array
    {
        return $this->openingHours;
    }

    public function addOpeningHours(OpeningHours $openingHours): self
    {
        $this->openingHours[$openingHours->getDay()] = $openingHours;

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $country): self
    {
        $this->countryCode = $country;

        return $this;
    }
}
