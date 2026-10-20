<?php

namespace App\Api\Odoo\Model;

use App\Doctrine\Entity\Sylius\CustomerInterface as BaseCustomerInterface;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Addressing\Model\AddressInterface;
use Sylius\Component\Core\Model\TaxRateInterface;

class Customer implements CustomerInterface
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
     * @var string|null
     *
     * @Serializer\Type("string")
     */
    public $commercialname;

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
    public $vat;

    public function __construct(BaseCustomerInterface $customer, AddressInterface $address, ?TaxRateInterface $taxRate)
    {
        $this->lastname = $customer->getLastName();
        $this->firstname = $customer->getFirstName();
        $this->commercialname = $address->getCompany();
        $this->mail = $customer->getEmail();
        $this->phone = $customer->getPhoneNumber();
        $this->vat = null !== $taxRate ? $taxRate->getCode() : '';
    }
}
