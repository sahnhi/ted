<?php

namespace App\Api\Shipup\DTO;

use App\Doctrine\Entity\Sylius\Address;
use JMS\Serializer\Annotation as Serializer;

class ShipupShippingAddress
{
    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("address1")
     */
    public string $address;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("address2")
     */
    public ?string $complementaryAddress;

    /**
     * @Serializer\Type("string")
     */
    public string $city;

    /**
     * @Serializer\Type("string")
     */
    public string $zip;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("country_code")
     */
    public string $countryCode;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("first_name")
     */
    public string $firstName;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("last_name")
     */
    public string $lastName;

    /**
     * @Serializer\Type("string")
     */
    public string $name;

    /**
     * @Serializer\Type("string")
     *
     * @Serializer\SerializedName("company_title")
     */
    public ?string $companyTitle;

    public function __construct(Address $address)
    {
        $this->address = $address->getStreet();
        $this->complementaryAddress = $address->getDetails();
        $this->city = $address->getCity();
        $this->zip = $address->getPostcode();
        $this->countryCode = $address->getCountryCode();
        $this->firstName = $address->getFirstName();
        $this->lastName = $address->getLastName();
        $this->name = $address->getFullName();
        $this->companyTitle = $address->getCompany();
    }
}
