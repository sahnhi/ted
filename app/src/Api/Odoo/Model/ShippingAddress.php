<?php

namespace App\Api\Odoo\Model;

use App\Doctrine\Entity\Sylius\Address;
use App\Doctrine\Entity\Sylius\CustomerInterface;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Addressing\Model\AddressInterface;

class ShippingAddress extends AbstractAddress
{
    /**
     * @var string|null
     *
     * @Serializer\Type("string")
     */
    public $notes;

    public function __construct(AddressInterface $address, CustomerInterface $customer)
    {
        parent::__construct($address, $customer);

        /* @var Address $address */
        $this->notes = $address->getDetails();
    }
}
