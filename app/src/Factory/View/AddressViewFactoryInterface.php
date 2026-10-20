<?php

namespace App\Factory\View;

use App\View\AddressTransmissionView;
use App\View\AddressView;
use Sylius\Component\Core\Model\AddressInterface;

interface AddressViewFactoryInterface
{
    public function create(AddressInterface $address): AddressView;

    public function createTransmission(AddressInterface $address): AddressTransmissionView;
}
