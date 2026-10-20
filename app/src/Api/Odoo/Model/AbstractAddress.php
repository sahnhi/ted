<?php

namespace App\Api\Odoo\Model;

use App\Doctrine\Entity\Sylius\CustomerInterface;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Addressing\Model\AddressInterface as BaseAddressInterface;

abstract class AbstractAddress implements AddressInterface
{
    /**
     * @Serializer\Type("string")
     */
    public string $lastname;

    /**
     * @Serializer\Type("string")
     */
    public string $firstname;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    public $mail;

    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     */
    public $phone;

    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     */
    public $street;

    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     */
    public $city;

    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     */
    public $zip;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    public $country;

    public function __construct(BaseAddressInterface $address, CustomerInterface $customer)
    {
        $this->lastname = $address->getLastName();
        $this->firstname = $address->getFirstName();
        $this->mail = $customer->getEmail();
        $this->phone = $address->getPhoneNumber();
        $this->street = $address->getStreet();
        $this->zip = $address->getPostcode();
        $this->country = $address->getCountryCode();
        $this->city = $address->getCity();
    }
}
