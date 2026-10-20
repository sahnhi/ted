<?php

namespace App\Factory\View;

use App\View\AddressTransmissionView;
use App\View\AddressView;
use Sylius\Component\Core\Model\AddressInterface;

final class AddressViewFactory implements AddressViewFactoryInterface
{
    public function create(AddressInterface $address): AddressView
    {
        $addressView = new AddressView();
        $addressView->firstName = trim((string) $address->getFirstName());
        $addressView->lastName = trim((string) $address->getLastName());
        $addressView->countryCode = trim((string) $address->getCountryCode());
        $addressView->street = trim((string) $address->getStreet());
        $addressView->city = trim((string) $address->getCity());
        $addressView->postcode = trim((string) $address->getPostcode());
        $addressView->provinceName = trim((string) $address->getProvinceName());
        $addressView->details = trim((string) $address->getDetails());
        $addressView->tva = trim((string) $address->getTva());

        return $addressView;
    }

    public function createTransmission(AddressInterface $address): AddressTransmissionView
    {
        $addressTransmissionView = new AddressTransmissionView();
        $addressTransmissionView->firstName = trim((string) $address->getFirstName());
        $addressTransmissionView->lastName = trim((string) $address->getLastName());
        $addressTransmissionView->countryCode = trim((string) $address->getCountryCode());
        $addressTransmissionView->street = trim((string) $address->getStreet());
        $addressTransmissionView->city = trim((string) $address->getCity());
        $addressTransmissionView->postcode = trim((string) $address->getPostcode());
        $addressTransmissionView->details = trim((string) $address->getDetails());
        $addressTransmissionView->phoneNumber = trim((string) $address->getPhoneNumber());

        return $addressTransmissionView;
    }
}
